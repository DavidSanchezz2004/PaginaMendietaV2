<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreQuoteRequest;
use App\Http\Requests\Facturador\UpdateQuoteRequest;
use App\Models\Quote;
use App\Models\Client;
use App\Models\CompanySetting;
use App\Services\Facturador\QuoteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador de Cotizaciones.
 * 
 * Acciones:
 *  - index: lista de cotizaciones
 *  - create: formulario crear
 *  - store: guardar
 *  - show: ver detalles
 *  - edit: editar
 *  - update: actualizar
 *  - destroy: eliminar
 *  - client: vista pública (sin auth) para cliente
 *  - pdf: generar PDF
 *  - createVersion: crear nueva versión
 *  - convertToInvoice: convertir a factura
 */
class QuoteController extends Controller
{
    public function __construct(
        private readonly QuoteService $quoteService,
    ) {
    }

    /**
     * Listado de cotizaciones.
     */
    public function index(Request $request): View
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('viewAny', Quote::class);

        $filters = $request->only(['estado', 'client_id', 'search']);
        $companyId = session('company_id');

        $query = Quote::where('company_id', $companyId)
            ->with('client', 'user')
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('search')) {
            $search = "%{$request->search}%";
            $query->whereAny(
                ['numero_cotizacion', 'codigo_interno', 'observacion'],
                'like',
                $search
            );
        }

        $quotes = $query->paginate(15);

        return view('facturador.cotizaciones.index', [
            'quotes' => $quotes,
            'filters' => $filters,
        ]);
    }

    /**
     * Formulario de creación.
     */
    public function create(): View
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('create', Quote::class);

        $companyId = session('company_id');
        $clients = Client::where('company_id', $companyId)
            ->orderBy('nombre_razon_social')
            ->get();

        return view('facturador.cotizaciones.create', [
            'clients' => $clients,
        ]);
    }

    /**
     * Guarda una nueva cotización.
     */
    public function store(StoreQuoteRequest $request): RedirectResponse
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('create', Quote::class);

        $companyId = session('company_id');
        $userId = auth()->id();

        $data = $request->only([
            'client_id',
            'fecha_emision',
            'fecha_vencimiento',
            'observacion',
            'correo',
            'numero_orden_compra',
            'codigo_moneda',
            'porcentaje_igv',
        ]);

        $items = json_decode($request->input('items_json', '[]'), true);

        $quote = $this->quoteService->create(
            $companyId,
            $userId,
            $request->client_id,
            $data,
            $items
        );

        return redirect()
            ->route('facturador.cotizaciones.show', $quote)
            ->with('success', 'Cotización creada correctamente.');
    }

    /**
     * Vista de detalles de cotización (admin).
     */
    public function show(Quote $quote): View
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('view', $quote);

        return view('facturador.cotizaciones.show', [
            'quote' => $quote->load('items', 'client', 'user'),
        ]);
    }

    /**
     * Formulario de edición.
     */
    public function edit(Quote $quote): View
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('update', $quote);

        $companyId = session('company_id');
        $clients = Client::where('company_id', $companyId)
            ->orderBy('nombre_razon_social')
            ->get();

        return view('facturador.cotizaciones.edit', [
            'quote' => $quote->load('items'),
            'clients' => $clients,
        ]);
    }

    /**
     * Actualiza una cotización (solo si está en draft).
     */
    public function update(UpdateQuoteRequest $request, Quote $quote): RedirectResponse
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('update', $quote);

        if ($quote->estado !== 'draft') {
            return redirect()
                ->route('facturador.cotizaciones.show', $quote)
                ->with('error', 'Solo se pueden editar cotizaciones en borrador.');
        }

        $data = $request->only([
            'fecha_emision',
            'fecha_vencimiento',
            'observacion',
            'correo',
            'numero_orden_compra',
            'porcentaje_igv',
        ]);

        $quote->update($data);

        // Actualizar items si se proporcionan
        if ($request->filled('items_json')) {
            $quote->items()->delete();
            $items = json_decode($request->input('items_json'), true);
            $this->quoteService->addItems($quote, $items);
        }

        $this->quoteService->recalculate($quote);

        return redirect()
            ->route('facturador.cotizaciones.show', $quote)
            ->with('success', 'Cotización actualizada correctamente.');
    }

    /**
     * Elimina una cotización (solo si está en draft).
     */
    public function destroy(Quote $quote): RedirectResponse
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('delete', $quote);

        if ($quote->estado !== 'draft') {
            return back()->with('error', 'Solo se pueden eliminar cotizaciones en borrador.');
        }

        $quote->delete();

        return redirect()
            ->route('facturador.cotizaciones.index')
            ->with('success', 'Cotización eliminada correctamente.');
    }

    /**
     * Marca como enviada y retorna JSON con URL pública.
     * POST /cotizaciones/{quote}/send
     */
    public function send(Quote $quote): JsonResponse
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('update', $quote);

        $this->quoteService->markAsSent($quote);

        return response()->json([
            'success' => true,
            'share_url' => $quote->getShareUrlAttribute(),
            'share_token' => $quote->share_token,
            'message' => 'Cotización enviada correctamente.',
        ]);
    }

    /**
     * Crea nueva versión de la cotización.
     * POST /cotizaciones/{quote}/versions
     */
    public function createVersion(Request $request, Quote $quote): RedirectResponse
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('update', $quote);

        $data = $request->only([
            'fecha_emision',
            'fecha_vencimiento',
            'observacion',
            'porcentaje_igv',
        ]);

        $items = $request->filled('items_json')
            ? json_decode($request->input('items_json'), true)
            : null;

        $newVersion = $this->quoteService->createVersion($quote, $data, $items);

        return redirect()
            ->route('facturador.cotizaciones.show', $newVersion)
            ->with('success', 'Nueva versión creada correctamente.');
    }

    /**
     * Convierte cotización aceptada a factura.
     * POST /cotizaciones/{quote}/to-invoice
     */
    public function convertToInvoice(Request $request, Quote $quote): RedirectResponse
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('update', $quote);

        if ($quote->estado !== 'accepted') {
            return back()->with('error', 'Solo se pueden convertir cotizaciones aceptadas.');
        }

        try {
            $overrideData = $request->only([
                'codigo_tipo_documento',
                'serie_documento',
                'numero_documento',
                'forma_pago',
            ]);

            $invoice = $this->quoteService->convertToInvoice($quote, $overrideData);

            return redirect()
                ->route('facturador.facturas.show', $invoice)
                ->with('success', 'Cotización convertida a factura correctamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al convertir: ' . $e->getMessage());
        }
    }

    /**
     * Genera PDF de la cotización.
     * GET /cotizaciones/{quote}/pdf
     */
    public function pdf(Quote $quote)
    {
        $this->ensureQuoteFeatureEnabled();
        $this->authorize('view', $quote);

        $quote->load('items', 'client', 'company');
        $settings = CompanySetting::firstOrCreate(
            ['company_id' => $quote->company_id],
            [
                'quote_enabled' => true,
                'primary_color' => '#013b33',
                'secondary_color' => '#eef7f5',
                'company_name' => $quote->company?->name,
                'ruc' => $quote->company?->ruc,
            ]
        );

        $items = $quote->items->map(fn ($item) => [
            'servicio' => $item->descripcion,
            'cantidad' => (float) $item->cantidad,
            'precio' => (float) $item->monto_valor_unitario,
            'total' => (float) $item->monto_valor_total,
        ]);

        return view('facturador.quotations.preview', [
            'cotNumber' => $quote->codigo_interno ?: $quote->numero_cotizacion,
            'fechaEmision' => $quote->fecha_emision,
            'fechaVencimiento' => $quote->fecha_vencimiento ?: $quote->fecha_emision,
            'clienteTipoDoc' => $quote->client?->codigo_tipo_documento ?: '6',
            'clienteNumeroDoc' => $quote->client?->numero_documento ?: '-',
            'clienteNombre' => strtoupper($quote->client?->nombre_cliente ?: 'SIN CLIENTE'),
            'descripcion' => $quote->observacion ?? '',
            'items' => $items,
            'subtotal' => (float) $quote->monto_total_gravado,
            'igv' => (float) $quote->monto_total_igv,
            'total' => (float) $quote->monto_total,
            'aplicaIgv' => (float) $quote->monto_total_igv > 0,
            'company' => $quote->company,
            'settings' => $settings,
            'quote' => $quote,
        ]);
    }

    private function ensureQuoteFeatureEnabled(): void
    {
        $role = auth()->user()?->role?->value ?? (string) auth()->user()?->role;

        if ($role === 'admin') {
            return;
        }

        $enabled = CompanySetting::where('company_id', session('company_id'))->value('quote_enabled');

        if ($enabled === false || $enabled === 0 || $enabled === '0') {
            abort(403, 'El cotizador no está habilitado para esta empresa.');
        }
    }
}
