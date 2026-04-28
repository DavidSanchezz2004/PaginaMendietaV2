<?php

namespace App\Http\Controllers\Facturador;

use App\Exports\PurchaseExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\StorePurchaseRequest;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\Facturador\OpenAiPdfExtractorService;
use App\Services\Facturador\ProviderService;
use App\Services\Facturador\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService  $purchaseService,
        private readonly ProviderService  $providerService,
    ) {}

    // ── CRUD ──────────────────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Purchase::class);

        $filters   = $request->only(['search', 'accounting_status', 'tipo_documento', 'fecha_desde', 'fecha_hasta', 'flow_status']);
        $purchases = $this->purchaseService->paginate(15, $filters);

        $stats = [
            'total_mes'       => Purchase::forActiveCompany()
                ->whereYear('fecha_emision', now()->year)
                ->whereMonth('fecha_emision', now()->month)
                ->sum('monto_total'),
            'listos_count'    => Purchase::forActiveCompany()
                ->where('accounting_status', 'listo')->count(),
            'pendientes_count' => Purchase::forActiveCompany()
                ->whereIn('accounting_status', ['incompleto', 'pendiente'])->count(),
            'total_count'     => Purchase::forActiveCompany()->count(),
        ];

        $company = \App\Models\Company::findOrFail(session('company_id'));

        return view('facturador.compras.index', compact('purchases', 'filters', 'stats', 'company'));
    }

    public function create(): View
    {
        $this->authorize('create', Purchase::class);

        return view('facturador.compras.create');
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        $this->authorize('create', Purchase::class);

        $validated = $request->validated();

        // Normalizar forma_pago: los selects mandan '01'/'02', BD espera '1'/'2'
        if (isset($validated['forma_pago'])) {
            $validated['forma_pago'] = ltrim($validated['forma_pago'], '0') ?: null;
        }

        $purchase = $this->purchaseService->create($validated);

        // Guardar ítems opcionales si se enviaron desde el formulario
        $itemsJson = $request->input('items_json', '[]');
        $items     = json_decode($itemsJson, true);
        if (is_array($items) && count($items) > 0) {
            $companyId = session('company_id');
            foreach ($items as $i => $item) {
                if (empty(trim($item['descripcion'] ?? ''))) {
                    continue;
                }
                PurchaseItem::create([
                    'purchase_id'    => $purchase->id,
                    'company_id'     => $companyId,
                    'correlativo'    => $i + 1,
                    'descripcion'    => substr(trim($item['descripcion']), 0, 500),
                    'unidad_medida'  => substr(trim($item['unidad_medida'] ?? ''), 0, 30) ?: null,
                    'cantidad'       => (float) ($item['cantidad'] ?? 0),
                    'valor_unitario' => (float) ($item['valor_unitario'] ?? 0),
                    'descuento'      => (float) ($item['descuento'] ?? 0),
                    'importe_venta'  => (float) ($item['importe_venta'] ?? 0),
                    'icbper'         => (float) ($item['icbper'] ?? 0),
                ]);
            }
        }

        return redirect()->route('facturador.compras.show', $purchase)
            ->with('success', "Compra #{$purchase->id} registrada correctamente.");
    }

    public function show(Purchase $purchase): View|RedirectResponse
    {
        // Si la compra pertenece a otra empresa del mismo usuario, dar mensaje útil
        if ((int) $purchase->company_id !== (int) session('company_id')) {
            $user    = Auth::user();
            $uRole   = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;
            $canSwitch = $uRole === 'admin'
                || \App\Models\CompanyUser::where('user_id', $user->id)
                    ->where('company_id', $purchase->company_id)
                    ->whereIn('role', ['admin', 'client'])
                    ->where('status', 'active')
                    ->exists();

            if ($canSwitch) {
                $companyName = \App\Models\Company::find($purchase->company_id)?->name
                    ?? "empresa #{$purchase->company_id}";

                return redirect()->route('facturador.compras.index')
                    ->with('warning', "Esta compra pertenece a \"{$companyName}\". Selecciona esa empresa en el selector del Facturador y vuelve a intentarlo.");
            }
        }

        $this->authorize('view', $purchase);
        $purchase->load([
            'provider',
            'user',
            'items',
            'letterCompensationDetails.compensation.letraCambio.invoice.client',
            'letterCompensationDetails.compensation.supplier',
        ]);

        return view('facturador.compras.show', compact('purchase'));
    }

    public function edit(Purchase $purchase): View
    {
        $this->authorize('update', $purchase);
        $purchase->load(['provider']);

        return view('facturador.compras.edit', compact('purchase'));
    }

    public function update(Request $request, Purchase $purchase): RedirectResponse
    {
        $this->authorize('update', $purchase);

        $validated = $request->validate([
            'codigo_tipo_documento'   => 'required|in:01,03,07,08,00',
            'serie_documento'         => 'nullable|string|max:10',
            'numero_documento'        => 'required|string|max:20',
            'fecha_emision'           => 'required|date',
            'fecha_vencimiento'       => 'nullable|date',
            'tipo_doc_proveedor'      => 'required|in:1,4,6,7,A,B,D,E,G',
            'numero_doc_proveedor'    => 'required|string|max:20',
            'razon_social_proveedor'  => 'required|string|max:200',
            'codigo_moneda'           => 'required|in:PEN,USD,EUR',
            'monto_tipo_cambio'       => 'nullable|numeric|min:0',
            'porcentaje_igv'          => 'required|integer|in:0,8,10,18',
            'base_imponible_gravadas' => 'required|numeric|min:0',
            'igv_gravadas'            => 'required|numeric|min:0',
            'monto_no_gravado'        => 'nullable|numeric|min:0',
            'monto_exonerado'         => 'nullable|numeric|min:0',
            'monto_total'             => 'required|numeric|min:0',
            'forma_pago'              => 'nullable|in:01,02,03,04,05,06,07,08',
            'observacion'             => 'nullable|string|max:500',
            'es_sujeto_detraccion'    => 'nullable|in:0,1',
            'monto_detraccion'        => 'nullable|numeric|min:0',
            'detraccion_leyenda'      => 'nullable|string|max:500',
            'detraccion_bien_codigo'  => 'nullable|string|max:50',
            'detraccion_bien_descripcion' => 'nullable|string|max:200',
            'detraccion_medio_pago'   => 'nullable|string|max:100',
            'detraccion_numero_cuenta' => 'nullable|string|max:50',
            'detraccion_porcentaje'   => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['forma_pago'])) {
            $validated['forma_pago'] = ltrim($validated['forma_pago'], '0') ?: null;
        }

        // Procesar datos de detracción
        if ((bool) ($validated['es_sujeto_detraccion'] ?? false)) {
            $validated['es_sujeto_detraccion'] = true;
            $validated['informacion_detraccion'] = [
                'leyenda'             => $validated['detraccion_leyenda'] ?? null,
                'bien_codigo'         => $validated['detraccion_bien_codigo'] ?? null,
                'bien_descripcion'    => $validated['detraccion_bien_descripcion'] ?? null,
                'medio_pago'          => $validated['detraccion_medio_pago'] ?? null,
                'numero_cuenta'       => $validated['detraccion_numero_cuenta'] ?? null,
                'porcentaje'          => $validated['detraccion_porcentaje'] ? (float) $validated['detraccion_porcentaje'] : null,
            ];
            
            // Convertir monto_detraccion a float
            if ($validated['monto_detraccion'] ?? null) {
                $validated['monto_detraccion'] = (float) $validated['monto_detraccion'];
                // Calcular monto neto
                $validated['monto_neto_detraccion'] = (float) $validated['monto_total'] - (float) $validated['monto_detraccion'];
            }
            
            // Limpiar campos individuales
            unset($validated['detraccion_leyenda'], $validated['detraccion_bien_codigo'], 
                  $validated['detraccion_bien_descripcion'], $validated['detraccion_medio_pago'],
                  $validated['detraccion_numero_cuenta'], $validated['detraccion_porcentaje']);
        } else {
            $validated['es_sujeto_detraccion'] = false;
            $validated['informacion_detraccion'] = null;
            $validated['monto_detraccion'] = null;
            $validated['monto_neto_detraccion'] = null;
        }

        $this->purchaseService->update($purchase, $validated);

        return redirect()->route('facturador.compras.show', $purchase)
            ->with('success', 'Compra actualizada correctamente.');
    }

    public function destroy(Purchase $purchase): RedirectResponse
    {
        $this->authorize('delete', $purchase);

        $this->purchaseService->delete($purchase);

        return redirect()->route('facturador.compras.index')
            ->with('success', 'Registro de compra eliminado.');
    }

    // ── Completado contable ───────────────────────────────────────────────

    /**
     * Devuelve datos de la compra + completitud + sugerencias autofill.
     * GET /facturador/compras/{purchase}/accounting  (AJAX)
     */
    public function getAccountingData(Purchase $purchase): JsonResponse
    {
        $this->authorize('view', $purchase);

        $purchase->load('provider');
        $completeness = $purchase->accounting_completeness;
        $suggestions  = $purchase->auto_fill_suggestions;

        return response()->json([
            'purchase' => [
                'id'                       => $purchase->id,
                'serie_numero'             => $purchase->serie_numero,
                'codigo_tipo_documento'    => $purchase->codigo_tipo_documento,
                'tipo_documento_label'     => ['01' => 'Factura', '03' => 'Boleta', '07' => 'N. Crédito', '08' => 'N. Débito', '00' => 'DUA'][$purchase->codigo_tipo_documento] ?? $purchase->codigo_tipo_documento,
                'proveedor'                => $purchase->razon_social_proveedor ?? '—',
                'ruc'                      => $purchase->numero_doc_proveedor ?? '—',
                'monto_total'              => number_format((float) $purchase->monto_total, 2),
                'codigo_moneda'            => $purchase->codigo_moneda ?? 'PEN',
                'fecha_emision'            => $purchase->fecha_emision?->format('d/m/Y') ?? '',
                'fecha_vencimiento'        => $purchase->fecha_vencimiento?->format('d/m/Y') ?? '',
                // Campos contables
                'forma_pago'               => $purchase->forma_pago ?? '',
                'tipo_operacion'           => $purchase->tipo_operacion ?? '',
                'tipo_compra'              => $purchase->tipo_compra?->value ?? '',
                'cuenta_contable'          => $purchase->cuenta_contable ?? '',
                'codigo_producto_servicio' => $purchase->codigo_producto_servicio ?? '',
                'glosa'                    => $purchase->glosa ?? '',
                'centro_costo'             => $purchase->centro_costo ?? '',
                'tipo_gasto'               => $purchase->tipo_gasto ?? '',
                'sucursal'                 => $purchase->sucursal ?? '',
                'comprador'                => $purchase->comprador ?? '',
                'es_anticipo'              => (bool) $purchase->es_anticipo,
                'es_documento_contingencia'=> (bool) $purchase->es_documento_contingencia,
                'es_sujeto_detraccion'     => (bool) $purchase->es_sujeto_detraccion,
                'es_sujeto_retencion'      => (bool) $purchase->es_sujeto_retencion,
                'es_sujeto_percepcion'     => (bool) $purchase->es_sujeto_percepcion,
                'lista_cuotas'             => $purchase->lista_cuotas ?? [],
                'accounting_status'        => $purchase->accounting_status?->value ?? 'incompleto',
            ],
            'completeness' => $completeness,
            'suggestions'  => $suggestions,
        ]);
    }

    /**
     * Guarda los campos contables y recalcula accounting_status.
     * PATCH /facturador/compras/{purchase}/accounting  (AJAX)
     */
    public function saveAccounting(Request $request, Purchase $purchase): JsonResponse
    {
        $this->authorize('update', $purchase);

        $validated = $request->validate([
            'tipo_operacion'            => 'required|string|max:10',
            'tipo_compra'               => 'required|string|max:4',
            'cuenta_contable'           => 'required|string|max:10',
            'codigo_producto_servicio'  => 'required|string|max:50',
            'forma_pago'                => 'nullable|in:01,02,03,04,05,06,07,08',
            'glosa'                     => 'nullable|string|max:500',
            'centro_costo'              => 'nullable|string|max:50',
            'tipo_gasto'                => 'nullable|string|max:20',
            'sucursal'                  => 'nullable|string|max:50',
            'comprador'                 => 'nullable|string|max:100',
            'es_anticipo'               => 'boolean',
            'es_documento_contingencia' => 'boolean',
            'es_sujeto_detraccion'      => 'boolean',
            'es_sujeto_retencion'       => 'boolean',
            'es_sujeto_percepcion'      => 'boolean',
            'cuota_1_fecha'             => 'nullable|date',
            'cuota_1_monto'             => 'nullable|numeric|min:0',
            'cuota_2_fecha'             => 'nullable|date',
            'cuota_2_monto'             => 'nullable|numeric|min:0',
        ]);

        $purchase = $this->purchaseService->saveAccounting($purchase, $validated);

        return response()->json([
            'success'           => true,
            'accounting_status' => $purchase->accounting_status->value,
            'label'             => $purchase->accounting_status->label(),
            'completeness'      => $purchase->accounting_completeness,
        ]);
    }

    /**
     * Cuenta compras listas en un rango para el modal de exportación.
     * GET /facturador/compras/export-count  (AJAX)
     */
    public function exportCount(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Purchase::class);

        $validated = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $count = Purchase::forActiveCompany()
            ->where('accounting_status', 'listo')
            ->where('es_sujeto_detraccion', false)
            ->whereBetween('fecha_emision', [$validated['from'], $validated['to']])
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Exporta compras listas a Excel SUNAT-ready.
     * GET /facturador/compras/export-excel?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function exportExcel(Request $request): BinaryFileResponse|RedirectResponse
    {
        $this->authorize('viewAny', Purchase::class);

        $validated = $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $companyId = session('company_id');
        $count = Purchase::forActiveCompany()
            ->where('accounting_status', 'listo')
            ->whereBetween('fecha_emision', [$validated['from'], $validated['to']])
            ->count();

        if ($count === 0) {
            return redirect()->route('facturador.compras.index')
                ->with('warning', 'No hay compras listas en ese rango de fechas.');
        }

        $filename = 'LibroCompras_' . str_replace('-', '', $validated['from']) . '_' . str_replace('-', '', $validated['to']) . '.xlsx';

        return Excel::download(
            new PurchaseExport($companyId, $validated['from'], $validated['to']),
            $filename
        );
    }

    /**
     * Lookup de proveedor por número de documento para autocompletar el formulario.
     * GET /facturador/compras/lookup-provider?doc=20123456789
     */
    public function lookupProvider(Request $request): JsonResponse
    {
        $this->authorize('create', Purchase::class);

        $doc = trim($request->query('doc', ''));
        if (strlen($doc) < 8) {
            return response()->json(['found' => false]);
        }

        $provider = \App\Models\Provider::forActiveCompany()
            ->where('numero_documento', $doc)
            ->first();

        if (! $provider) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found'            => true,
            'tipo_documento'   => $provider->tipo_documento,
            'razon_social'     => $provider->nombre_razon_social,
        ]);
    }

    /**
     * Formulario de subida / importación manual de comprobantes.
     * GET /facturador/compras/subir
     */
    public function subirForm(): View
    {
        $this->authorize('create', Purchase::class);

        $company = \App\Models\Company::findOrFail(session('company_id'));

        return view('facturador.compras.subir', compact('company'));
    }

    /**
     * Procesa una importación manual (JSON pegado en el formulario web).
     * POST /facturador/compras/subir
     */
    public function subirProcesar(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Purchase::class);

        $request->validate([
            'json_data' => 'required|string',
        ]);

        $data = json_decode($request->input('json_data'), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return back()->withErrors(['json_data' => 'JSON inválido: ' . json_last_error_msg()]);
        }

        $data['company_id'] = session('company_id');

        ['data' => $data, 'validacion' => $validacion] = $this->purchaseService->validarYAsignar($data);

        $purchase = $this->purchaseService->create($data);

        $msg = $validacion['ok']
            ? "Compra #{$purchase->id} importada correctamente."
            : "Compra #{$purchase->id} importada con observaciones: " . implode(', ', $validacion['errores']);

        return redirect()->route('facturador.compras.show', $purchase)
            ->with($validacion['ok'] ? 'success' : 'warning', $msg);
    }

    /**
     * Extrae datos de un PDF con OpenAI y devuelve JSON para mostrar preview.
     * POST /facturador/compras/subir-pdf-extraer
     */
    public function subirPdfExtraer(Request $request): JsonResponse
    {
        $this->authorize('create', Purchase::class);

        if (! $request->hasFile('pdf') || ! $request->file('pdf')->isValid()) {
            return response()->json(['error' => 'Archivo PDF no recibido o inválido.'], 422);
        }

        if ($request->file('pdf')->getClientMimeType() !== 'application/pdf') {
            return response()->json(['error' => 'Solo se aceptan archivos PDF.'], 422);
        }

        if ($request->file('pdf')->getSize() > 10 * 1024 * 1024) {
            return response()->json(['error' => 'El archivo supera los 10 MB.'], 422);
        }

        try {
            $extractor = new OpenAiPdfExtractorService();
            $data      = $extractor->extract($request->file('pdf'));
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Error al procesar el PDF. Intenta de nuevo.'], 500);
        }

        return response()->json($data);
    }

    /**
     * Confirma e importa los datos extraídos del PDF (ya revisados por el usuario).
     * POST /facturador/compras/subir-pdf-confirmar
     */
    public function subirPdfConfirmar(Request $request): RedirectResponse
    {
        $this->authorize('create', Purchase::class);

        $request->validate([
            'numero_doc_proveedor'    => 'required|string',
            'razon_social_proveedor'  => 'required|string',
            'codigo_tipo_documento'   => 'required|string',
            'serie_documento'         => 'required|string',
            'numero_documento'        => 'required|string',
            'fecha_emision'           => 'required|date',
            'monto_total'             => 'required|numeric|min:0',
            'es_sujeto_detraccion'    => 'nullable|in:0,1',
            'monto_detraccion'        => 'nullable|numeric|min:0',
            'informacion_detraccion_json' => 'nullable|json',
            'es_sujeto_retencion'     => 'nullable|in:0,1',
            'retention_base'          => 'nullable|numeric|min:0',
            'retention_percentage'    => 'nullable|numeric|min:0|max:100',
            'retention_amount'        => 'nullable|numeric|min:0',
            'net_total'               => 'nullable|numeric|min:0',
            'retention_info_json'     => 'nullable|json',
        ]);

        $data               = $request->only([
            'numero_doc_proveedor', 'razon_social_proveedor',
            'codigo_tipo_documento', 'serie_documento', 'numero_documento',
            'fecha_emision', 'fecha_vencimiento',
            'base_imponible_gravadas', 'igv_gravadas', 'monto_total', 'codigo_moneda',
            'es_sujeto_detraccion', 'monto_detraccion',
            'es_sujeto_retencion', 'retention_base', 'retention_percentage', 'retention_amount', 'net_total',
        ]);

        // Defaultear campos monetarios a 0 cuando vengan vacíos (ej: GRE no tiene montos)
        $data['monto_total']             = (isset($data['monto_total'])             && $data['monto_total']             !== '') ? (float) $data['monto_total']             : 0.0;
        $data['base_imponible_gravadas'] = (isset($data['base_imponible_gravadas']) && $data['base_imponible_gravadas'] !== '') ? (float) $data['base_imponible_gravadas'] : 0.0;
        $data['igv_gravadas']            = (isset($data['igv_gravadas'])            && $data['igv_gravadas']            !== '') ? (float) $data['igv_gravadas']            : 0.0;

        // Campos contables sugeridos por la IA (opcionales)
        $tipoOp    = trim($request->input('contable_tipo_operacion', ''));
        $tipoCompra = trim($request->input('contable_tipo_compra', ''));
        $cuenta    = trim($request->input('contable_cuenta_contable', ''));
        $codPS     = trim($request->input('contable_codigo_producto_servicio', ''));
        $formaPago = trim($request->input('contable_forma_pago', ''));
        $glosa     = trim($request->input('contable_glosa', ''));

        if ($tipoOp)     $data['tipo_operacion']           = $tipoOp;
        if ($tipoCompra) $data['tipo_compra']              = $tipoCompra;
        if ($cuenta)     $data['cuenta_contable']          = substr($cuenta, 0, 10); // Truncar a 10 chars (BD limit)
        if ($codPS)      $data['codigo_producto_servicio'] = substr($codPS, 0, 50);  // Truncar a 50 chars (BD limit)
        if ($formaPago)  $data['forma_pago']               = ltrim($formaPago, '0') ?: null;
        if ($glosa)      $data['glosa']                    = substr($glosa, 0, 500);

        // Convertir es_sujeto_detraccion a boolean
        if (isset($data['es_sujeto_detraccion'])) {
            $data['es_sujeto_detraccion'] = (bool) $data['es_sujeto_detraccion'];
        }
        
        // Convertir es_sujeto_retencion a boolean
        if (isset($data['es_sujeto_retencion'])) {
            $data['es_sujeto_retencion'] = (bool) $data['es_sujeto_retencion'];
        }
        
        // Convertir monto_detraccion a float, 0 si vacío (BD no permite null)
        if (isset($data['monto_detraccion']) && $data['monto_detraccion'] !== '') {
            $data['monto_detraccion'] = (float) $data['monto_detraccion'];
        } else {
            $data['monto_detraccion'] = 0; // ← Cambio: 0 en lugar de null
        }

        // Procesar información de RETENCIÓN (Compras)
        if ((bool) ($request->input('es_sujeto_retencion') ?? false)) {
            $data['es_sujeto_retencion'] = true;
            $data['retention_enabled'] = true;
            $data['retention_base'] = (float) ($request->input('retention_base') ?? $request->input('monto_total') ?? 0);
            $data['retention_percentage'] = (float) ($request->input('retention_percentage') ?? 0);
            $data['retention_amount'] = (float) ($request->input('retention_amount') ?? 0);
            $data['net_total'] = (float) ($request->input('net_total') ?? 0);
            
            // Si alguno está vacío, recalcular
            if (!$data['retention_amount'] || !$data['net_total']) {
                $data['retention_amount'] = ($data['retention_base'] * $data['retention_percentage']) / 100;
                $data['net_total'] = $data['retention_base'] - $data['retention_amount'];
            }
            
            $retentionInfoJson = $request->input('retention_info_json');
            if (!empty($retentionInfoJson)) {
                try {
                    $data['retention_info'] = json_decode($retentionInfoJson, true);
                } catch (\Exception $e) {
                    $data['retention_info'] = null;
                }
            }
            
            // Guardar monto retenido en el campo legacy
            $data['monto_retencion'] = $data['retention_amount'];
        } else {
            $data['es_sujeto_retencion'] = false;
            $data['retention_enabled'] = false;
            $data['retention_base'] = null;
            $data['retention_percentage'] = null;
            $data['retention_amount'] = null;
            $data['net_total'] = null;
            $data['retention_info'] = null;
            $data['monto_retencion'] = 0; // ← Cambio: 0 en lugar de null
        }

        // Procesar información completa de detracción
        // Opción 1: JSON desde formulario de subida (subir.blade.php)
        $infoDetraccionJson = $request->input('informacion_detraccion_json');
        if (!empty($infoDetraccionJson)) {
            try {
                $data['informacion_detraccion'] = json_decode($infoDetraccionJson, true);
            } catch (\Exception $e) {
                // Si hay error en JSON, dejar vacío
            }
        }
        
        // Opción 2: Campos individuales desde formulario de edición (edit.blade.php)
        if (empty($data['informacion_detraccion']) && (bool) ($request->input('es_sujeto_detraccion') ?? false)) {
            $data['informacion_detraccion'] = [
                'leyenda'             => $request->input('detraccion_leyenda'),
                'bien_codigo'         => $request->input('detraccion_bien_codigo'),
                'bien_descripcion'    => $request->input('detraccion_bien_descripcion'),
                'medio_pago'          => $request->input('detraccion_medio_pago'),
                'numero_cuenta'       => $request->input('detraccion_numero_cuenta'),
                'porcentaje'          => $request->input('detraccion_porcentaje') ? (float) $request->input('detraccion_porcentaje') : null,
            ];
        }
        
        // Calcular monto neto (total - detracción) si hay detracción
        if ($data['monto_detraccion'] && $data['monto_total']) {
            $data['monto_neto_detraccion'] = (float) $data['monto_total'] - (float) $data['monto_detraccion'];
        }

        $data['company_id'] = session('company_id');

        ['data' => $data, 'validacion' => $validacion] = $this->purchaseService->validarYAsignar($data);
        $purchase = $this->purchaseService->create($data);

        // Guardar ítems si vienen del formulario
        $itemsJson = $request->input('items_json', '[]');
        $items     = json_decode($itemsJson, true);
        if (is_array($items) && count($items) > 0) {
            $companyId = session('company_id');
            foreach ($items as $i => $item) {
                if (empty(trim($item['descripcion'] ?? ''))) {
                    continue;
                }
                PurchaseItem::create([
                    'purchase_id'    => $purchase->id,
                    'company_id'     => $companyId,
                    'correlativo'    => $i + 1,
                    'descripcion'    => substr(trim($item['descripcion']), 0, 500),
                    'unidad_medida'  => substr(trim($item['unidad_medida'] ?? ''), 0, 30) ?: null,
                    'cantidad'       => (float) ($item['cantidad'] ?? 0),
                    'valor_unitario' => (float) ($item['valor_unitario'] ?? 0),
                    'descuento'      => (float) ($item['descuento'] ?? 0),
                    'importe_venta'  => (float) ($item['importe_venta'] ?? 0),
                    'icbper'         => (float) ($item['icbper'] ?? 0),
                ]);
            }
        }

        // ════════════════════════════════════════════════════════════════
        // Procesar y guardar datos de GRE (Guía de Remisión Electrónica)
        // ════════════════════════════════════════════════════════════════
        $greNumero = trim($request->input('gre_numero', ''));
        if (!empty($greNumero)) {
            // Preparar datos de GRE para guardar
            $greData = [
                'gre_numero'                    => substr($greNumero, 0, 30),
                'gre_fecha_inicio_traslado'     => $request->input('gre_fecha_inicio_traslado') ?: null,
                'gre_motivo_traslado'           => substr(trim($request->input('gre_motivo_traslado', '')), 0, 50),
                'gre_punto_partida'             => $request->input('gre_punto_partida') ?: null,
                'gre_punto_llegada'             => $request->input('gre_punto_llegada') ?: null,
                'gre_destinatario_ruc'          => substr(trim($request->input('gre_destinatario_ruc', '')), 0, 12),
                'gre_destinatario_razon_social' => substr(trim($request->input('gre_destinatario_razon_social', '')), 0, 255),
                'gre_documento_relacionado'     => $request->input('gre_documento_relacionado') ?: null,
                'gre_bienes_descripcion'        => $request->input('gre_bienes_descripcion') ?: null,
                'gre_cantidad_bienes'           => $request->input('gre_cantidad_bienes') ? (int) $request->input('gre_cantidad_bienes') : null,
                'gre_unidad_medida'             => substr(trim($request->input('gre_unidad_medida', '')), 0, 30),
                'gre_peso_bruto'                => $request->input('gre_peso_bruto') ? (float) $request->input('gre_peso_bruto') : null,
                'gre_unidad_medida_peso'        => substr(trim($request->input('gre_unidad_medida_peso', '')), 0, 10),
                'gre_privado_transporte'        => (bool) $request->input('gre_privado_transporte', 0),
                'gre_retorno_vehiculo_vacio'    => (bool) $request->input('gre_retorno_vehiculo_vacio', 0),
                'gre_transbordo_programado'     => (bool) $request->input('gre_transbordo_programado', 0),
                'gre_notas'                     => $request->input('gre_notas') ?: null,
                'gre_registrado_en'             => now(),
            ];

            // Procesar datos de vehículo (JSON)
            $placaVehiculo = trim($request->input('gre_placa_vehiculo', ''));
            if (!empty($placaVehiculo)) {
                $greData['gre_datos_vehiculo'] = json_encode([
                    'placa'  => $placaVehiculo,
                    'tipo'   => 'Principal', // Por defecto
                ]);
            }

            // Procesar datos de conductor (JSON)
            $conductorNombre = trim($request->input('gre_conductor_nombre', ''));
            if (!empty($conductorNombre)) {
                $greData['gre_datos_conductor'] = json_encode([
                    'nombre'           => $conductorNombre,
                    'dni'              => trim($request->input('gre_conductor_dni', '')),
                    'numero_licencia'  => trim($request->input('gre_conductor_licencia', '')),
                ]);
            }

            // Actualizar la compra con los datos de GRE
            $purchase->update($greData);
        }

        $msg = $validacion['ok']
            ? "Compra #{$purchase->id} importada correctamente desde PDF."
            : "Compra #{$purchase->id} importada con observaciones: " . implode(', ', $validacion['errores']);

        return redirect()->route('facturador.compras.index')
            ->with($validacion['ok'] ? 'success' : 'warning', $msg);
    }
}
