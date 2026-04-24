<?php

namespace App\Http\Controllers\Api;

use App\Exports\PurchaseExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Purchase;
use App\Services\Facturador\AccountingAssignmentService;
use App\Services\Facturador\LetraCambioService;
use App\Services\Facturador\ProviderService;
use App\Services\Facturador\PurchaseValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Endpoints para n8n y automatización externa.
 * Auth: Bearer token (companies.api_token) via middleware 'api.token'.
 */
class CompraApiController extends Controller
{
    public function __construct(
        private readonly PurchaseValidationService  $validator,
        private readonly AccountingAssignmentService $assignmentService,
        private readonly ProviderService             $providerService,
        private readonly LetraCambioService          $letraService,
    ) {}

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Retorna la empresa autenticada (inyectada por ValidateApiToken). */
    private function company(Request $request): Company
    {
        return $request->attributes->get('api_company');
    }

    // ── POST /api/compras/importar ────────────────────────────────────────

    /**
     * Recibe el JSON extraído del PDF por n8n/IA, valida, asigna y guarda.
     *
     * Body mínimo requerido:
     * {
     *   "codigo_tipo_documento": "01",
     *   "serie_documento": "F001",
     *   "numero_documento": "0000123",
     *   "fecha_emision": "2026-04-09",
     *   "tipo_doc_proveedor": "6",
     *   "numero_doc_proveedor": "20123456789",
     *   "razon_social_proveedor": "PROVEEDOR SAC",
     *   "codigo_moneda": "PEN",
     *   "base_imponible_gravadas": 100.00,
     *   "igv_gravadas": 18.00,
     *   "monto_total": 118.00
     * }
     */
    public function importar(Request $request): JsonResponse
    {
        $company = $this->company($request);

        try {
            $data = $request->validate([
                'codigo_tipo_documento'   => 'required|string|max:2',
                'serie_documento'         => 'nullable|string|max:10',
                'numero_documento'        => 'required|string|max:20',
                'fecha_emision'           => 'required|date',
                'fecha_vencimiento'       => 'nullable|date',
                'tipo_doc_proveedor'      => 'required|string|max:2',
                'numero_doc_proveedor'    => 'required|string|max:20',
                'razon_social_proveedor'  => 'required|string|max:200',
                'codigo_moneda'           => 'nullable|in:PEN,USD,EUR',
                'monto_tipo_cambio'       => 'nullable|numeric|min:0',
                'porcentaje_igv'          => 'nullable|integer|in:0,8,10,18',
                'base_imponible_gravadas' => 'nullable|numeric|min:0',
                'igv_gravadas'            => 'nullable|numeric|min:0',
                'monto_exonerado'         => 'nullable|numeric|min:0',
                'monto_no_gravado'        => 'nullable|numeric|min:0',
                'monto_isc'               => 'nullable|numeric|min:0',
                'monto_icbper'            => 'nullable|numeric|min:0',
                'otros_tributos'          => 'nullable|numeric|min:0',
                'monto_descuento'         => 'nullable|numeric|min:0',
                'monto_total'             => 'required|numeric|min:0',
                'forma_pago'              => 'nullable|string|max:2',
                'glosa'                   => 'nullable|string|max:500',
                'monto_detraccion'        => 'nullable|numeric|min:0',
                'monto_retencion'         => 'nullable|numeric|min:0',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error'   => 'Datos inválidos.',
                'errores' => $e->errors(),
            ], 422);
        }

        $data['company_id']   = $company->id;
        $data['codigo_moneda'] = $data['codigo_moneda'] ?? 'PEN';
        $data['porcentaje_igv'] = $data['porcentaje_igv'] ?? 18;

        // ── 1. Validación automática ──────────────────────────────────────
        $validacion = $this->validator->validate($data);

        // ── 2. Asignación contable ────────────────────────────────────────
        if ($validacion['ok']) {
            $data = $this->assignmentService->assignToArray($data, $company->id);
        }

        // ── 3. Calcular accounting_status ────────────────────────────────
        $data['accounting_status'] = $validacion['status'];
        $data['errores_validacion'] = $validacion['ok'] ? null : $validacion['errores'];

        // ── 4. Sincronizar proveedor ──────────────────────────────────────
        $provider = $this->providerService->findOrCreate(
            $data['tipo_doc_proveedor'],
            $data['numero_doc_proveedor'],
            $data['razon_social_proveedor']
        );
        $data['provider_id'] = $provider->id;

        // ── 5. Persistir ──────────────────────────────────────────────────
        $purchase = DB::transaction(fn () => Purchase::create($data));
        $purchase->load('provider');

        return response()->json([
            'ok'               => true,
            'purchase_id'      => $purchase->id,
            'accounting_status'=> $purchase->accounting_status,
            'errores'          => $validacion['errores'],
            'purchase'         => $this->formatPurchase($purchase),
        ], 201);
    }

    // ── POST /api/compras/validar ─────────────────────────────────────────

    /**
     * Solo devuelve el resultado de la validación, sin guardar.
     * Útil para preview en n8n antes de importar.
     */
    public function validar(Request $request): JsonResponse
    {
        $data = $request->all();
        $result = $this->validator->validate($data);

        return response()->json([
            'ok'     => $result['ok'],
            'status' => $result['status'],
            'errores'=> $result['errores'],
        ]);
    }

    // ── POST /api/compras/{purchase}/canjear-letras ───────────────────────

    /**
     * Genera letras de cambio para una compra existente.
     *
     * Body:
     * {
     *   "cuotas": [
     *     {"dias": 30, "porcentaje": 33.33},
     *     {"dias": 60, "porcentaje": 33.33},
     *     {"dias": 90, "porcentaje": 33.34}
     *   ],
     *   "lugar_giro": "LIMA",
     *   "banco": "BCP",
     *   "banco_cuenta": "123-456789-0-01"
     * }
     */
    public function canjeLetras(Request $request, Purchase $purchase): JsonResponse
    {
        $company = $this->company($request);

        if ((int) $purchase->company_id !== (int) $company->id) {
            return response()->json(['error' => 'Acceso denegado.'], 403);
        }

        try {
            $data = $request->validate([
                'cuotas'            => 'required|array|min:1|max:36',
                'cuotas.*.dias'     => 'required|integer|min:1|max:1080',
                'cuotas.*.porcentaje'=> 'nullable|numeric|min:0.01|max:100',
                'lugar_giro'        => 'nullable|string|max:100',
                'banco'             => 'nullable|string|max:100',
                'banco_oficina'     => 'nullable|string|max:50',
                'banco_cuenta'      => 'nullable|string|max:50',
                'banco_dc'          => 'nullable|string|max:10',
                'cuenta_contable'   => 'nullable|string|max:10',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Datos inválidos.', 'errores' => $e->errors()], 422);
        }

        $letras = $this->letraService->canjear(
            $purchase,
            $data['cuotas'],
            $data
        );

        return response()->json([
            'ok'             => true,
            'total_letras'   => $letras->count(),
            'monto_total'    => $letras->sum('monto'),
            'letras'         => $letras->map(fn ($l) => [
                'id'               => $l->id,
                'numero_letra'     => $l->numero_letra,
                'fecha_vencimiento'=> $l->fecha_vencimiento->format('Y-m-d'),
                'monto'            => $l->monto,
                'estado'           => $l->estado,
            ]),
        ], 201);
    }

    // ── GET /api/compras/exportar-excel ───────────────────────────────────

    /**
     * Exporta el libro de compras en formato SUNAT.
     * Query params: from=2026-04-01&to=2026-04-30
     */
    public function exportarExcel(Request $request): BinaryFileResponse
    {
        $company = $this->company($request);

        $from = $request->query('from', now()->startOfMonth()->toDateString());
        $to   = $request->query('to',   now()->endOfMonth()->toDateString());

        $filename = sprintf(
            'libro-compras-%s-%s.xlsx',
            $company->ruc,
            now()->format('Ymd_His')
        );

        return Excel::download(
            new PurchaseExport($company->id, $from, $to),
            $filename
        );
    }

    // ── POST /api/empresa/generar-token ──────────────────────────────────

    /**
     * Genera o regenera el api_token de la empresa.
     * Este endpoint NO tiene middleware api.token (es bootstrap).
     * Proteger a nivel de servidor/IP en producción.
     */
    public function generarToken(Request $request): JsonResponse
    {
        $request->validate([
            'ruc' => 'required|string|size:11',
        ]);

        $company = Company::where('ruc', $request->input('ruc'))->first();

        if (! $company) {
            return response()->json(['error' => 'Empresa no encontrada.'], 404);
        }

        $token = $company->generateApiToken();

        return response()->json([
            'ok'         => true,
            'company_id' => $company->id,
            'ruc'        => $company->ruc,
            'api_token'  => $token,
            'tip'        => 'Usar en n8n: Header Authorization: Bearer ' . $token,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function formatPurchase(Purchase $purchase): array
    {
        return [
            'id'                      => $purchase->id,
            'codigo_tipo_documento'   => $purchase->codigo_tipo_documento,
            'serie_numero'            => $purchase->serie_numero,
            'fecha_emision'           => $purchase->fecha_emision?->format('Y-m-d'),
            'proveedor'               => $purchase->razon_social_proveedor,
            'ruc_proveedor'           => $purchase->numero_doc_proveedor,
            'monto_total'             => $purchase->monto_total,
            'igv'                     => $purchase->igv_gravadas,
            'base_imponible'          => $purchase->base_imponible_gravadas,
            'moneda'                  => $purchase->codigo_moneda,
            'cuenta_contable'         => $purchase->cuenta_contable,
            'cuenta_igv'              => $purchase->cuenta_igv,
            'tipo_compra'             => $purchase->tipo_compra?->value,
            'accounting_status'       => $purchase->accounting_status?->value ?? $purchase->accounting_status,
        ];
    }
}
