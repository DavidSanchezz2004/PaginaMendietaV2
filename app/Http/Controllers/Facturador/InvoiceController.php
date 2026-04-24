<?php

namespace App\Http\Controllers\Facturador;

use App\Enums\AccountingStatusEnum;
use App\Exports\InvoiceExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StoreInvoiceRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\SpotDetraccion;
use App\Services\Facturador\ClientService;
use App\Services\Facturador\FeasyService;
use App\Services\Facturador\InvoiceService;
use App\Services\Facturador\LetraService;
use App\Services\Facturador\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        private readonly LetraService   $letraService,
        private readonly FeasyService   $feasyService,
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
            $items = array_map(fn (array $item): array => array_merge([
                'monto_valor_unitario'    => 0,
                'monto_precio_unitario'   => 0,
                'monto_valor_total'       => 0,
                'monto_igv'               => 0,
                'monto_total'             => 0,
                'codigo_indicador_afecto' => '10',
            ], array_intersect_key($item, array_flip([
                'correlativo', 'codigo_interno', 'codigo_unidad_medida', 'descripcion', 'cantidad',
            ]))), $items);

            // Forzar defaults para campos que no aplican a GRE
            $validated['client_id']           = null;
            $validated['forma_pago']           = null;
            $validated['monto_total_gravado']  = 0;
            $validated['monto_total_igv']      = 0;
            $validated['monto_total']          = 0;
            $validated['porcentaje_igv']       = $validated['porcentaje_igv'] ?? 18;
            $validated['codigo_moneda']        = $validated['codigo_moneda'] ?? 'PEN';
            $validated['lista_guias']          = null; // GRE no adjunta guías
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

            // ── Auto-llenar SIEMPRE campos adicionales con info de SPOT ──────────────────
            // Feasy requiere estos campos con SPOT activo
            $validated['informacion_adicional'] = $validated['informacion_adicional'] ?? [];
            
            // Campo 1: SIEMPRE va la cuenta de detracción
            $validated['informacion_adicional']['informacion_adicional_1'] = $d['cuenta_banco_detraccion'] ?? '';
            
            // Campo 2: SIEMPRE va información completa de la detracción con saltos de línea
            $montoDet = $validated['informacion_detraccion']['monto_detraccion'];
            $neto     = round($total - $montoDet, 2);
            $codigoBien = $d['codigo_bbss_sujeto_detraccion'] ?? '';
            $codigoMedio = $d['codigo_medio_pago_detraccion'] ?? '001';
            
            // Buscar descripciones
            $bienServ = SpotDetraccion::where('codigo', $codigoBien)->first();
            $descBien = $bienServ?->descripcion ?? "Código $codigoBien";
            // Remover el porcentaje entre paréntesis (ej: "(9%)")
            $descBien = preg_replace('/\s*\(\d+%\)$/', '', $descBien);
            
            // Array de medios de pago
            $mediosPago = [
                '001' => '001 - Deposito en cuenta',
                '002' => '002 - Transferencia de fondos',
                '003' => '003 - Transferencia de fondos',
            ];
            $descMedio = $mediosPago[$codigoMedio] ?? "Código $codigoMedio";
            
            // Construir información COMPLETA con saltos de línea
            $validated['informacion_adicional']['informacion_adicional_2'] =
                "Leyenda:\n" .
                "Operacion sujeta al Sistema de Pago de Obligaciones Tributarias con el Gobierno Central\n" .
                "Bien o Servicio: $codigoBien - $descBien\n" .
                "Porcentaje de detraccion: " . number_format($pct, 0) . "%\n" .
                "Monto detraccion: PEN " . number_format($montoDet, 2, '.', ',') . "\n" .
                "Nro. Cta. Banco de la Nacion: " . ($d['cuenta_banco_detraccion'] ?? '') . "\n" .
                "Medio de pago: $descMedio\n" .
                "Monto neto pendiente de pago: PEN " . number_format($neto, 2, '.', ',');
        }

        // ── Re-calcular monto_retencion server-side para integridad ──────
        $clientForRetention = ! empty($validated['client_id'])
            ? \App\Models\Client::forActiveCompany()->find($validated['client_id'])
            : null;
        $totalForRetention = (float) ($validated['monto_total'] ?? 0);
        $hasDetraction = ! empty($validated['indicador_detraccion']);
        $mustRetain = ! $hasDetraction && (
            ! empty($validated['indicador_retencion'])
            || ((bool) ($clientForRetention?->is_retainer_agent) && $totalForRetention > 700)
        );

        if ($mustRetain) {
            $r        = $validated['informacion_retencion'] ?? [];
            $pct      = (float) ($r['porcentaje_retencion'] ?? 3);
            $total    = $totalForRetention;
            $validated['indicador_retencion'] = true;
            $validated['informacion_retencion'] = $r;
            $validated['informacion_retencion']['codigo_retencion'] = $r['codigo_retencion'] ?? '62';
            $validated['informacion_retencion']['porcentaje_retencion'] = $pct;
            $validated['informacion_retencion']['monto_retencion'] = round($total * $pct / 100, 2);
            $validated['informacion_retencion']['monto_base_imponible_retencion'] = $total;
            
            // ── Copiar a campos del modelo Invoice ──────────────────────────────────
            $validated['retention_enabled'] = true;
            $validated['has_retention'] = true;
            $validated['retention_base'] = $total;
            $validated['retention_percentage'] = $pct;
            $validated['retention_amount'] = $validated['informacion_retencion']['monto_retencion'];
            $validated['net_total'] = round($total - $validated['retention_amount'], 2);
            $validated['retention_info'] = $validated['informacion_retencion'];
            $validated['total_before_retention'] = $total;
            $validated['total_after_retention'] = $validated['net_total'];

            $codigoRet = $validated['informacion_retencion']['codigo_retencion'];
            $monedaRet = $validated['codigo_moneda'] ?? 'PEN';
            $validated['informacion_adicional'] = $validated['informacion_adicional'] ?? [];
            $validated['informacion_adicional']['informacion_adicional_3'] =
                "Informacion Retencion:\n" .
                "Codigo retencion: {$codigoRet}\n" .
                "Base imponible retencion: {$monedaRet} " . number_format($total, 2, '.', ',') . "\n" .
                "Porcentaje retencion: " . number_format($pct, 2) . "%\n" .
                "Monto retencion: {$monedaRet} " . number_format($validated['retention_amount'], 2, '.', ',') . "\n" .
                "Monto neto pendiente de pago: {$monedaRet} " . number_format($validated['net_total'], 2, '.', ',');
        } else {
            // Limpiar si no hay retención
            $validated['indicador_retencion'] = false;
            $validated['informacion_retencion'] = null;
            $validated['retention_enabled'] = false;
            $validated['has_retention'] = false;
            $validated['retention_base'] = null;
            $validated['retention_percentage'] = null;
            $validated['retention_amount'] = null;
            $validated['net_total'] = null;
            $validated['retention_info'] = null;
            $validated['total_before_retention'] = null;
            $validated['total_after_retention'] = null;
        }

        $invoice = $this->invoiceService->create($validated, $items);

        // ── Generar letras de cambio si es crédito ─────────────────────────────────
        if ((string) $validated['forma_pago'] === '2' && !empty($validated['lista_cuotas'])) {
            $letras = $this->letraService->generateFromInvoice($invoice);
            if ($letras->count() > 0) {
                $msg = "Comprobante {$invoice->serie_numero} creado. Se generaron {$letras->count()} letras de cambio.";
            } else {
                $msg = "Comprobante {$invoice->serie_numero} creado como borrador.";
            }
        } else {
            $msg = "Comprobante {$invoice->serie_numero} creado como borrador.";
        }

        $label = $tipoDoc === '09' ? 'Guía de Remisión' : 'Comprobante';

        return redirect()->route('facturador.invoices.show', $invoice)
            ->with('success', $msg);
    }

    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice = $this->invoiceService->findWithItems($invoice->id);
        $invoice->load('payments');

        return view('facturador.invoices.show', compact('invoice'));
    }

    public function payload(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice = $this->invoiceService->findWithItems($invoice->id);

        try {
            $data = $this->feasyService->previewPayload($invoice);
        } catch (\Throwable $e) {
            $data = ['error' => $e->getMessage()];
        }

        return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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

        // Primero, desasociar cualquier guía de remisión que referencia esta factura
        \App\Models\GuiaRemision::where('invoice_id', $invoice->id)->update([
            'invoice_id' => null,
            'estado' => 'generated', // Volver a estado de generada
        ]);

        // Luego eliminar los items
        $invoice->items()->delete();
        
        // Finalmente eliminar la factura
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

    // ══════════════════════════════════════════════════════════════════════
    // Completado contable + Exportación Excel
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Devuelve datos del comprobante + completitud + sugerencias autofill.
     * GET /facturador/invoices/{invoice}/accounting  (AJAX)
     */
    public function getAccountingData(Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $invoice->load('client');
        $completeness = $invoice->accounting_completeness;
        $suggestions  = $invoice->auto_fill_suggestions;

        return response()->json([
            'invoice' => [
                'id'                       => $invoice->id,
                'serie_numero'             => $invoice->serie_numero,
                'codigo_tipo_documento'    => $invoice->codigo_tipo_documento,
                'tipo_documento_label'     => ['01' => 'Factura', '03' => 'Boleta', '07' => 'N. Crédito', '08' => 'N. Débito', '09' => 'GRE'][$invoice->codigo_tipo_documento] ?? $invoice->codigo_tipo_documento,
                'cliente'                  => $invoice->client?->nombre_razon_social ?? '—',
                'ruc'                      => $invoice->client?->numero_documento ?? '—',
                'tipo_doc_cliente'         => $invoice->client?->tipo_documento ?? '',
                'monto_total'              => number_format((float) $invoice->monto_total, 2),
                'codigo_moneda'            => $invoice->codigo_moneda ?? 'PEN',
                'fecha_emision'            => $invoice->fecha_emision?->format('d/m/Y') ?? '',
                'fecha_vencimiento'        => $invoice->fecha_vencimiento?->format('d/m/Y') ?? '',
                'indicador_detraccion'     => (bool) $invoice->indicador_detraccion,
                // Campos contables guardados
                'forma_pago'               => $invoice->forma_pago ?? '',
                'tipo_operacion'           => $invoice->tipo_operacion ?? '',
                'tipo_venta'               => $invoice->tipo_venta ?? '',
                'cuenta_contable'          => $invoice->cuenta_contable ?? '',
                'codigo_producto_servicio' => $invoice->codigo_producto_servicio ?? '',
                'glosa'                    => $invoice->glosa ?? '',
                'centro_costo'             => $invoice->centro_costo ?? '',
                'tipo_gasto'               => $invoice->tipo_gasto ?? '',
                'sucursal'                 => $invoice->sucursal ?? '',
                'vendedor'                 => $invoice->vendedor ?? '',
                'es_anticipo'              => (bool) $invoice->es_anticipo,
                'es_documento_contingencia'=> (bool) $invoice->es_documento_contingencia,
                'es_sujeto_retencion'      => (bool) $invoice->es_sujeto_retencion,
                'es_sujeto_percepcion'     => (bool) $invoice->es_sujeto_percepcion,
                'lista_cuotas'             => $invoice->lista_cuotas ?? [],
                'accounting_status'        => $invoice->accounting_status?->value ?? 'incompleto',
            ],
            'completeness' => $completeness,
            'suggestions'  => $suggestions,
        ]);
    }

    /**
     * Guarda los campos contables y recalcula accounting_status.
     * PATCH /facturador/invoices/{invoice}/accounting  (AJAX)
     */
    public function saveAccounting(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize('view', $invoice);

        $validated = $request->validate([
            'tipo_operacion'            => 'required|string|max:10',
            'tipo_venta'                => 'required|string|max:10',
            'cuenta_contable'           => 'required|string|max:10',
            'codigo_producto_servicio'  => 'required|string|max:50',
            'forma_pago'                => 'required|in:1,2',
            'glosa'                     => 'nullable|string|max:500',
            'centro_costo'              => 'nullable|string|max:50',
            'tipo_gasto'                => 'nullable|string|max:20',
            'sucursal'                  => 'nullable|string|max:50',
            'vendedor'                  => 'nullable|string|max:100',
            'es_anticipo'               => 'boolean',
            'es_documento_contingencia' => 'boolean',
            'es_sujeto_retencion'       => 'boolean',
            'es_sujeto_percepcion'      => 'boolean',
            // Cuotas (solo si crédito)
            'cuota_1_fecha'             => 'nullable|date',
            'cuota_1_monto'             => 'nullable|numeric|min:0',
            'cuota_2_fecha'             => 'nullable|date',
            'cuota_2_monto'             => 'nullable|numeric|min:0',
        ]);

        // Construir cuotas si el pago es a crédito
        $cuotas = $invoice->lista_cuotas ?? [];
        if ($validated['forma_pago'] === '2') {
            $cuotas = [];
            if (!empty($validated['cuota_1_fecha']) && !empty($validated['cuota_1_monto'])) {
                $cuotas[] = [
                    'fecha_pago' => $validated['cuota_1_fecha'],
                    'monto'      => (float) $validated['cuota_1_monto'],
                    'moneda'     => $invoice->codigo_moneda ?? 'PEN',
                ];
            }
            if (!empty($validated['cuota_2_fecha']) && !empty($validated['cuota_2_monto'])) {
                $cuotas[] = [
                    'fecha_pago' => $validated['cuota_2_fecha'],
                    'monto'      => (float) $validated['cuota_2_monto'],
                    'moneda'     => $invoice->codigo_moneda ?? 'PEN',
                ];
            }
        }

        $invoice->fill([
            'tipo_operacion'            => $validated['tipo_operacion'],
            'tipo_venta'                => $validated['tipo_venta'],
            'cuenta_contable'           => $validated['cuenta_contable'],
            'codigo_producto_servicio'  => $validated['codigo_producto_servicio'],
            'forma_pago'                => $validated['forma_pago'],
            'glosa'                     => $validated['glosa'] ?? null,
            'centro_costo'              => $validated['centro_costo'] ?? null,
            'tipo_gasto'                => $validated['tipo_gasto'] ?? null,
            'sucursal'                  => $validated['sucursal'] ?? null,
            'vendedor'                  => $validated['vendedor'] ?? null,
            'es_anticipo'               => (bool) ($validated['es_anticipo'] ?? false),
            'es_documento_contingencia' => (bool) ($validated['es_documento_contingencia'] ?? false),
            'es_sujeto_retencion'       => (bool) ($validated['es_sujeto_retencion'] ?? false),
            'es_sujeto_percepcion'      => (bool) ($validated['es_sujeto_percepcion'] ?? false),
            'lista_cuotas'              => !empty($cuotas) ? $cuotas : $invoice->lista_cuotas,
        ]);

        // Recalcular accounting_status
        $completeness = $invoice->accounting_completeness;
        $invoice->accounting_status = $completeness['status'];
        $invoice->save();

        return response()->json([
            'success'           => true,
            'accounting_status' => $invoice->accounting_status->value,
            'label'             => $invoice->accounting_status->label(),
            'completeness'      => $invoice->accounting_completeness,
        ]);
    }

    /**
     * Cuenta comprobantes listos en un rango para el modal de exportación.
     * GET /facturador/invoices/export-count  (AJAX)
     */
    public function exportCount(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $validated = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $count = Invoice::where('company_id', session('company_id'))
            ->where('accounting_status', 'listo')
            ->whereBetween('fecha_emision', [$validated['from'], $validated['to']])
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Exporta comprobantes listos a Excel SUNAT-ready.
     * GET /facturador/invoices/export-excel?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('viewAny', Invoice::class);

        $validated = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $companyId = session('company_id');
        $count = Invoice::where('company_id', $companyId)
            ->where('accounting_status', 'listo')
            ->whereBetween('fecha_emision', [$validated['from'], $validated['to']])
            ->count();

        if ($count === 0) {
            return redirect()->route('facturador.invoices.index')
                ->with('warning', 'No hay comprobantes listos en ese rango de fechas.');
        }

        $filename = 'LibroVentas_' . str_replace('-', '', $validated['from']) . '_' . str_replace('-', '', $validated['to']) . '.xlsx';

        return Excel::download(
            new InvoiceExport($companyId, $validated['from'], $validated['to']),
            $filename
        );
    }

    /**
     * Muestra formulario para crear factura desde una guía de remisión.
     * GET /facturador/guias/{guia}/factura/crear
     */
    public function create_from_guia(\App\Models\GuiaRemision $guia): View
    {
        $this->authorize('create', Invoice::class);

        // Validar que guía está lista para facturar
        if ($guia->estado !== 'generated' || $guia->invoice_id) {
            abort(403, 'Esta guía ya fue facturada o no está en estado correcto');
        }

        $guia->load('purchase', 'client', 'clientAddress', 'items');

        $suggestions = $this->invoiceService->getDocumentSuggestions();

        return view('facturador.invoices.create-from-guia', [
            'guia' => $guia,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Crea factura desde una guía de remisión.
     * POST /facturador/guias/{guia}/factura
     */
    public function store_from_guia(
        \App\Models\GuiaRemision $guia,
        \App\Http\Requests\Facturador\CreateInvoiceFromGuiaRequest $request
    ): RedirectResponse {
        $this->authorize('create', Invoice::class);

        try {
            // Usar el servicio de facturación desde guía
            $invoiceService = app(\App\Services\InvoiceService::class);
            
            // Generar número automáticamente si no viene
            $validated = $request->validated();
            if (empty($validated['numero_documento'])) {
                $suggestions = $invoiceService->getDocumentSuggestions();
                $validated['numero_documento'] = $suggestions['numero'];
            }
            
            $invoice = $invoiceService->create_from_guia($guia, $validated);

            // Si forma_pago es crédito, generar letras automáticamente
            if ($request->forma_pago === '2' && $request->lista_cuotas) {
                $this->letraService->generateFromInvoice($invoice);
            }

            return redirect(route('facturador.invoices.show', $invoice))
                ->with('success', "Factura {$invoice->serie_numero} creada desde guía {$guia->numero}");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al crear factura desde guía: " . $e->getMessage(), [
                'guia_id' => $guia->id,
                'exception' => $e,
            ]);

            return back()->with('error', 'Error al crear factura: ' . $e->getMessage());
        }
    }
}
