<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\GenerateGuiaRequest;
use App\Models\ClientAddress;
use App\Models\GuiaRemision;
use App\Models\Purchase;
use App\Services\GuiaRemisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestión de guías de remisión.
 * Flujo: Preview → Generate
 */
class GuiaRemisionController extends Controller
{
    public function __construct(
        private GuiaRemisionService $service
    ) {}

    /**
     * Preview de guía antes de generar.
     * Permite revisar datos de compra y cliente.
     */
    public function preview(Purchase $purchase)
    {
        Log::info("=== PREVIEW REQUEST ===", [
            'purchase_id' => $purchase->id,
            'purchase_status' => $purchase->status,
            'purchase_client_id' => $purchase->client_id,
        ]);

        $this->authorize('create', GuiaRemision::class);

        // Validar estado de compra
        if ($purchase->status !== 'assigned') {
            Log::warning("Preview blocked: wrong status", [
                'expected' => 'assigned',
                'actual' => $purchase->status,
            ]);
            return back()->with('error', 'La compra debe tener cliente asignado (status: assigned)');
        }

        if ($purchase->guias()->exists()) {
            Log::warning("Preview blocked: guia already exists", ['purchase_id' => $purchase->id]);
            return back()->with('warning', 'Esta compra ya tiene guía generada');
        }

        // Cargar datos
        $purchase->load('provider', 'client', 'items');
        
        Log::info("Purchase loaded", [
            'client_id' => $purchase->client?->id,
            'items_count' => $purchase->items->count(),
        ]);

        $addresses = $purchase->client->addresses()->get();

        Log::info("Addresses loaded", [
            'count' => $addresses->count(),
        ]);

        if ($addresses->isEmpty()) {
            Log::warning("Preview blocked: no addresses", ['client_id' => $purchase->client->id]);
            return back()->with('error', 'El cliente no tiene direcciones registradas');
        }

        // Generar preview
        $preview = $this->service->preview($purchase, $addresses->first());

        Log::info("Preview generated successfully", [
            'purchase_id' => $purchase->id,
            'preview_keys' => array_keys($preview),
        ]);

        return view('facturador.guias.preview', [
            'purchase' => $purchase,
            'addresses' => $addresses,
            'preview' => $preview,
        ]);
    }

    /**
     * Genera guía de remisión.
     */
    public function generate(Purchase $purchase, GenerateGuiaRequest $request)
    {
        $this->authorize('create', GuiaRemision::class);

        try {
            $address = ClientAddress::findOrFail($request->client_address_id);

            $guia = $this->service->generate(
                $purchase,
                $address,
                $request->motivo,
                $request->items_prices ?? []
            );

            Log::info("Guía generada exitosamente: {$guia->numero}", [
                'guia_id' => $guia->id,
                'purchase_id' => $purchase->id,
            ]);

            return redirect(route('facturador.guias.show', $guia))
                ->with('success', "Guía {$guia->numero} generada exitosamente");
        } catch (\Exception $e) {
            Log::error("Error al generar guía: " . $e->getMessage(), [
                'purchase_id' => $purchase->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error al generar guía: ' . $e->getMessage());
        }
    }

    /**
     * Lista todas las guías de remisión.
     */
    public function index()
    {
        $this->authorize('viewAny', GuiaRemision::class);

        $query = GuiaRemision::query()
            ->with('purchase', 'client', 'items', 'invoice')
            ->where('company_id', session('company_id'))
            ->orderByDesc('created_at');

        // Filtros
        if ($search = request('search')) {
            $query->where('numero', 'like', "%{$search}%")
                  ->orWhereHas('purchase', fn($q) => $q->where('serie_numero', 'like', "%{$search}%"));
        }

        if ($estado = request('estado')) {
            $query->where('estado', $estado);
        }

        if ($from = request('from')) {
            $query->whereDate('fecha_emision', '>=', $from);
        }

        if ($to = request('to')) {
            $query->whereDate('fecha_emision', '<=', $to);
        }

        $guias = $query->paginate(15);

        // Stats
        $stats = [
            'total_guias' => GuiaRemision::where('company_id', session('company_id'))->count(),
            'pending' => GuiaRemision::where('company_id', session('company_id'))
                ->where('estado', 'generated')
                ->count(),
            'invoiced' => GuiaRemision::where('company_id', session('company_id'))
                ->where('estado', 'invoiced')
                ->count(),
            'draft' => GuiaRemision::where('company_id', session('company_id'))
                ->where('estado', 'draft')
                ->count(),
        ];

        return view('facturador.guias.index', [
            'guias' => $guias,
            'stats' => $stats,
        ]);
    }

    /**
     * Muestra detalles de una guía.
     */
    public function show(GuiaRemision $guia)
    {
        $this->authorize('view', $guia);

        $guia->load('purchase', 'client', 'clientAddress', 'items', 'invoice');

        return view('facturador.guias.show', [
            'guia' => $guia,
        ]);
    }
}
