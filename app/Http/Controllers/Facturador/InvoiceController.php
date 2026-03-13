<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\SpotDetraccion;
use App\Services\Facturador\ClientService;
use App\Services\Facturador\InvoiceService;
use App\Services\Facturador\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Controlador de Facturas del Facturador.
 * Delgado: toda la lógica de negocio en InvoiceService.
 *
 * Acciones adicionales más allá del CRUD:
 *  emit()       → POST .../invoices/{invoice}/emit
 *  consult()    → POST .../invoices/{invoice}/consult
 *  downloadXml() → GET .../invoices/{invoice}/xml
 */
class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly ClientService  $clientService,
        private readonly ProductService $productService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $filters  = $request->only(['estado', 'serie', 'search']);
        $invoices = $this->invoiceService->paginate(15, $filters);

        $stats = [
            'total_mes'         => (float) Invoice::forActiveCompany()
                                    ->whereYear('fecha_emision', now()->year)
                                    ->whereMonth('fecha_emision', now()->month)
                                    ->where('estado', '!=', 'voided')
                                    ->sum('monto_total'),
            'total_mes_sin_igv' => (float) Invoice::forActiveCompany()
                                    ->whereYear('fecha_emision', now()->year)
                                    ->whereMonth('fecha_emision', now()->month)
                                    ->where('estado', '!=', 'voided')
                                    ->sum('monto_total_gravado'),
            'total_mes_igv'     => (float) Invoice::forActiveCompany()
                                    ->whereYear('fecha_emision', now()->year)
                                    ->whereMonth('fecha_emision', now()->month)
                                    ->where('estado', '!=', 'voided')
                                    ->sum('monto_total_igv'),
            'aceptados_count' => Invoice::forActiveCompany()
                                    ->whereIn('estado', ['sent', 'consulted'])
                                    ->whereYear('fecha_emision', now()->year)
                                    ->whereMonth('fecha_emision', now()->month)
                                    ->count(),
            'aceptados_monto' => (float) Invoice::forActiveCompany()
                                    ->whereIn('estado', ['sent', 'consulted'])
                                    ->whereYear('fecha_emision', now()->year)
                                    ->whereMonth('fecha_emision', now()->month)
                                    ->sum('monto_total'),
            'atencion_count'  => Invoice::forActiveCompany()
                                    ->whereIn('estado', ['draft', 'ready', 'error'])
                                    ->count(),
            'error_count'     => Invoice::forActiveCompany()
                                    ->where('estado', 'error')
                                    ->count(),
            'por_tipo'        => Invoice::forActiveCompany()
                                    ->whereYear('fecha_emision', now()->year)
                                    ->whereMonth('fecha_emision', now()->month)
                                    ->where('estado', '!=', 'voided')
                                    ->selectRaw('codigo_tipo_documento, COUNT(*) as total')
                                    ->groupBy('codigo_tipo_documento')
                                    ->pluck('total', 'codigo_tipo_documento')
                                    ->toArray(),
        ];

        $company = \App\Models\Company::findOrFail(session('company_id'));

        return view('facturador.invoices.index', compact('invoices', 'filters', 'stats', 'company'));
    }

    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        $clients     = $this->clientService->allActive();
        $products    = $this->productService->allActive();
        $suggestions = $this->invoiceService->getDocumentSuggestions();
        $spotDetracciones = SpotDetraccion::activos()->get();

        return view('facturador.invoices.create', compact('clients', 'products', 'suggestions', 'spotDetracciones'));
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items     = $validated['items'];
        unset($validated['items']);

        $tipoDoc = $validated['codigo_tipo_documento'] ?? '';

        if ($tipoDoc === '09') {
            // GRE: ítems sin montos, sin cliente, sin forma de pago
            $items = array_map(fn (array $item): array => array_intersect_key($item, array_flip([
                'correlativo', 'codigo_interno', 'codigo_unidad_medida', 'descripcion', 'cantidad',
            ])), $items);

            // Forzar defaults para campos que no aplican a GRE
            $validated['client_id']           = null;
            $validated['forma_pago']           = null;
            $validated['monto_total_gravado']  = 0;
            $validated['monto_total_igv']      = 0;
            $validated['monto_total']          = 0;
            $validated['porcentaje_igv']       = $validated['porcentaje_igv'] ?? 18;
            $validated['codigo_moneda']        = $validated['codigo_moneda'] ?? 'PEN';
        } else {
            // Calcular monto_valor_unitario y monto_valor_total si no los envió el JS
            $igvRate = (float) ($validated['porcentaje_igv'] ?? 18) / 100;
            $items   = array_map(function (array $item) use ($igvRate): array {
                $precio   = (float) ($item['monto_precio_unitario'] ?? 0);
                $cantidad = (float) ($item['cantidad'] ?? 1);
                $afecto   = $item['codigo_indicador_afecto'] ?? '10';

                if (empty($item['monto_valor_unitario'])) {
                    $item['monto_valor_unitario'] = $afecto === '10'
                        ? $precio / (1 + $igvRate)
                        : $precio;
                }
                if (empty($item['monto_valor_total'])) {
                    $item['monto_valor_total'] = (float) $item['monto_valor_unitario'] * $cantidad;
                }
                if (empty($item['monto_total'])) {
                    $item['monto_total'] = $precio * $cantidad;
                }

                return $item;
            }, $items);
        }

        // user_id se inyecta desde el usuario autenticado (no del input)
        $validated['user_id'] = $request->user()->id;

        // ── Re-calcular monto_detraccion server-side para integridad ──────
        if (! empty($validated['indicador_detraccion']) && ! empty($validated['informacion_detraccion'])) {
            $d        = $validated['informacion_detraccion'];
            $pct      = (float) ($d['porcentaje_detraccion'] ?? 0);
            $total    = (float) ($validated['monto_total'] ?? 0);
            $validated['informacion_detraccion']['monto_detraccion'] = round($total * $pct / 100, 2);
        }

        $invoice = $this->invoiceService->create($validated, $items);

        $label = $tipoDoc === '09' ? 'Guía de Remisión' : 'Comprobante';

        return redirect()->route('facturador.invoices.show', $invoice)
            ->with('success', "{$label} {$invoice->serie_numero} creado como borrador.");
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice = $this->invoiceService->findWithItems($invoice->id);
        $invoice->load('payments');

        return view('facturador.invoices.show', compact('invoice'));
    }

    // ══════════════════════════════════════════════════════════════════════
    // Acciones Feasy
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Emite la factura a Feasy/SUNAT.
     * POST /facturador/invoices/{invoice}/emit
     */
    public function emit(Invoice $invoice): RedirectResponse
    {
        $this->authorize('emit', $invoice);

        try {
            $invoice = $this->invoiceService->emit($invoice);

            $message = $invoice->estado_feasy->isAccepted()
                ? "Factura {$invoice->serie_numero} aceptada por SUNAT."
                : "Factura {$invoice->serie_numero}: {$invoice->mensaje_respuesta_sunat}";

            $flashKey = $invoice->estado_feasy->isAccepted() ? 'success' : 'warning';

        } catch (RuntimeException $e) {
            return redirect()->route('facturador.invoices.show', $invoice)
                ->with('error', 'Error al emitir: ' . $e->getMessage());
        }

        return redirect()->route('facturador.invoices.show', $invoice)
            ->with($flashKey, $message);
    }

    /**
     * Consulta el estado del comprobante en Feasy.
     * POST /facturador/invoices/{invoice}/consult
     */
    public function consult(Invoice $invoice): RedirectResponse
    {
        $this->authorize('consult', $invoice);

        try {
            $invoice = $this->invoiceService->consult($invoice);

            $flash = $invoice->estado_feasy->isAccepted() ? 'success' : 'info';
            $msg   = "Consulta completada: " . ($invoice->mensaje_respuesta_sunat ?? 'OK');

        } catch (RuntimeException $e) {
            return redirect()->route('facturador.invoices.show', $invoice)
                ->with('error', 'Error al consultar: ' . $e->getMessage());
        }

        return redirect()->route('facturador.invoices.show', $invoice)
            ->with($flash, $msg);
    }

    /**
     * Descarga el XML del comprobante desde storage privado.
     * GET /facturador/invoices/{invoice}/xml
     */
    public function downloadXml(Invoice $invoice): StreamedResponse
    {
        $this->authorize('downloadXml', $invoice);

        if (! $invoice->xml_path || ! Storage::disk('local')->exists($invoice->xml_path)) {
            abort(404, 'El archivo XML no está disponible.');
        }

        return Storage::disk('local')->download(
            $invoice->xml_path,
            $invoice->nombre_archivo_xml ?? 'comprobante.xml',
            ['Content-Type' => 'application/xml']
        );
    }

    /**
     * Elimina un comprobante en estado borrador o error.
     * DELETE /facturador/invoices/{invoice}
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->items()->delete();
        $invoice->delete();

        return redirect()
            ->route('facturador.invoices.index')
            ->with('success', 'Comprobante eliminado correctamente.');
    }

    /**
     * Anula un comprobante ya enviado a SUNAT.
     * POST /facturador/invoices/{invoice}/void
     */
    public function void(Invoice $invoice): RedirectResponse
    {
        $this->authorize('void', $invoice);

        $motivo = trim(request()->input('motivo', 'Anulado'));
        if (empty($motivo)) {
            $motivo = 'Anulado';
        }

        try {
            $invoice = $this->invoiceService->void($invoice, $motivo);
            $status  = $invoice->estado->value === 'voided' ? 'success' : 'error';
            $msg     = $status === 'success'
                ? "Comprobante {$invoice->serie_numero} anulado correctamente."
                : "Error al anular: " . ($invoice->last_error ?? 'Error desconocido.');
        } catch (\RuntimeException $e) {
            $status = 'error';
            $msg    = $e->getMessage();
        }

        return redirect()
            ->route('facturador.invoices.show', $invoice)
            ->with($status, $msg);
    }

    /**
     * Registra un cobro/pago para un comprobante.
     * POST /facturador/invoices/{invoice}/payments
     */
    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('view', $invoice);

        $validated = $request->validate([
            'metodo'     => 'required|string|max:50',
            'monto'      => 'required|numeric|min:0.01',
            'referencia' => 'nullable|string|max:150',
            'notas'      => 'nullable|string|max:500',
        ]);

        $invoice->payments()->create($validated);

        return redirect()->route('facturador.invoices.show', $invoice)
            ->with('success', 'Cobro registrado correctamente.');
    }

    /**
     * Elimina un cobro registrado.
     * DELETE /facturador/invoices/{invoice}/payments/{payment}
     */
    public function destroyPayment(Invoice $invoice, InvoicePayment $payment): RedirectResponse
    {
        $this->authorize('view', $invoice);
        abort_if($payment->invoice_id !== $invoice->id, 403);

        $payment->delete();

        return redirect()->route('facturador.invoices.show', $invoice)
            ->with('success', 'Cobro eliminado correctamente.');
    }
}
