<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseClientController extends Controller
{
    /**
     * Mostrar formulario para asignar cliente a compra.
     */
    public function assignForm(Purchase $purchase)
    {
        $this->authorize('update', $purchase);

        // Bloquear cambio si ya tiene guía generada (no se puede cambiar cliente después)
        if (in_array($purchase->status, ['guided', 'partially_invoiced', 'invoiced'])) {
            return back()->with('warning', 'No se puede cambiar el cliente porque ya se generó una guía de remisión.');
        }

        $clients = Client::where('company_id', session('company_id'))
            ->orderBy('nombre_razon_social')
            ->get();

        return view('facturador.purchases.assign-client', [
            'purchase' => $purchase,
            'clients' => $clients,
        ]);
    }

    /**
     * Asignar cliente a compra.
     */
    public function assign(Purchase $purchase, Request $request)
    {
        $this->authorize('update', $purchase);

        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
        ]);

        try {
            $client = Client::findOrFail($validated['client_id']);

            // Validar que el cliente pertenece a la misma empresa
            if ($client->company_id !== session('company_id')) {
                return back()->with('error', 'Cliente no válido para esta empresa');
            }

            // Actualizar compra
            $purchase->update([
                'client_id' => $client->id,
                'status' => 'assigned',
            ]);

            Log::info("Cliente asignado a compra", [
                'purchase_id' => $purchase->id,
                'client_id' => $client->id,
                'user_id' => auth()->id(),
            ]);

            return redirect(route('facturador.purchases.guia-flow', $purchase))
                ->with('success', "Cliente {$client->nombre_razon_social} asignado exitosamente");
        } catch (\Exception $e) {
            Log::error("Error al asignar cliente: " . $e->getMessage(), [
                'purchase_id' => $purchase->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error al asignar cliente: ' . $e->getMessage());
        }
    }
}
