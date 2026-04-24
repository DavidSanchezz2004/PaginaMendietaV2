<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientAddressController extends Controller
{
    /**
     * Mostrar direcciones del cliente.
     */
    public function index(Client $client)
    {
        $this->authorize('update', $client);

        $client->load('addresses');

        return view('facturador.clients.addresses', [
            'client' => $client,
        ]);
    }

    /**
     * Guardar nueva dirección.
     */
    public function store(Client $client, Request $request)
    {
        $this->authorize('update', $client);

        $validated = $request->validate([
            'type' => 'required|in:fiscal,delivery',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'is_default' => 'required|in:0,1',
        ]);

        try {
            // Convertir a booleano
            $validated['is_default'] = (bool) $validated['is_default'];

            // Si es la primera dirección o se marca como default, actualizar otras
            if ($validated['is_default']) {
                $client->addresses()->update(['is_default' => false]);
            }

            $address = $client->addresses()->create($validated);

            Log::info("Dirección creada para cliente", [
                'client_id' => $client->id,
                'address_id' => $address->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Dirección agregada exitosamente');
        } catch (\Exception $e) {
            Log::error("Error al crear dirección: " . $e->getMessage(), [
                'client_id' => $client->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error al agregar dirección');
        }
    }

    /**
     * Actualizar dirección.
     */
    public function update(Client $client, ClientAddress $address, Request $request)
    {
        $this->authorize('update', $client);

        if ($address->client_id !== $client->id) {
            return back()->with('error', 'Dirección no válida');
        }

        $validated = $request->validate([
            'type' => 'required|in:fiscal,delivery',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        try {
            $address->update($validated);

            Log::info("Dirección actualizada", [
                'address_id' => $address->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Dirección actualizada');
        } catch (\Exception $e) {
            Log::error("Error al actualizar dirección: " . $e->getMessage(), [
                'address_id' => $address->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error al actualizar dirección');
        }
    }

    /**
     * Eliminar dirección.
     */
    public function destroy(Client $client, ClientAddress $address)
    {
        $this->authorize('update', $client);

        if ($address->client_id !== $client->id) {
            return back()->with('error', 'Dirección no válida');
        }

        if ($client->addresses()->count() <= 1) {
            return back()->with('error', 'No puedes eliminar la única dirección del cliente');
        }

        try {
            $address->delete();

            Log::info("Dirección eliminada", [
                'address_id' => $address->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Dirección eliminada');
        } catch (\Exception $e) {
            Log::error("Error al eliminar dirección: " . $e->getMessage(), [
                'address_id' => $address->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error al eliminar dirección');
        }
    }

    /**
     * Marcar dirección como predeterminada.
     */
    public function setDefault(Client $client, ClientAddress $address)
    {
        $this->authorize('update', $client);

        if ($address->client_id !== $client->id) {
            return back()->with('error', 'Dirección no válida');
        }

        try {
            $client->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);

            Log::info("Dirección marcada como predeterminada", [
                'address_id' => $address->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('success', 'Dirección predeterminada actualizada');
        } catch (\Exception $e) {
            Log::error("Error al actualizar dirección predeterminada: " . $e->getMessage(), [
                'address_id' => $address->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error al actualizar');
        }
    }
}
