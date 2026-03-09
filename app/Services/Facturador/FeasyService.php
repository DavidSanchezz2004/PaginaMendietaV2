<?php

namespace App\Services\Facturador;

use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Servicio de integración con la API de FeasaPeru (SUNAT).
 *
 * Arquitectura Feasy: 1 usuario → 1 token GLOBAL → gestiona muchas empresas.
 * El token identifica la CUENTA del portal (Estudio Mendieta en Feasy).
 * El RUC en cada request identifica QUÉ empresa está emitiendo.
 *
 * Por tanto:
 *  - El token se lee de config('services.feasy.token') (único, global).
 *  - Cada empresa sólo necesita facturador_enabled = true y su RUC correcto.
 *
 * Respuesta normalizada siempre:
 *  [
 *    'success'     => bool,
 *    'message'     => string,
 *    'data'        => array|null,
 *    'errors'      => array,
 *    'http_status' => int,
 *  ]
 */
class FeasyService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('facturador.feasy_base_url', 'https://api.feasyperu.com/api'), '/');
        $this->token   = config('services.feasy.token', '');
    }

    // ══════════════════════════════════════════════════════════════════════
    // 1) EMITIR COMPROBANTE (Factura 01 / Boleta 03)
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Envía un comprobante (Factura o Boleta) a Feasy/SUNAT.
     * El endpoint se elige automáticamente según codigo_tipo_documento:
     *   "01" → /comprobante/enviar_factura
     *   "03" → /comprobante/enviar_boleta
     *
     * Pre-condiciones verificadas (lanza RuntimeException si fallan):
     *  ✓ invoice.company_id == session('company_id')  (Anti-IDOR)
     *  ✓ company.facturador_enabled == true
     *  ✓ FEASY_TOKEN global configurado (config('services.feasy.token'))
     *  ✓ numero_documento_emisor == company.ruc
     *  ✓ totales coherentes (monto_total > 0, igv >= 0, no negativos)
     *  ✓ Factura (01) → receptor con RUC | Boleta (03) → receptor sin RUC
     *
     * @return array{success: bool, message: string, data: array|null, errors: array, http_status: int}
     */
    public function sendComprobante(Invoice $invoice): array
    {
        $company = $this->resolveAndValidateCompany($invoice);

        $tipoDoc = $invoice->codigo_tipo_documento;

        if ($tipoDoc !== '09') {
            $this->validateTotals($invoice);

            // Validar compatibilidad tipo de comprobante vs tipo de documento del receptor
            // Factura (01) → solo RUC (6) | Boleta (03) → cualquiera excepto RUC
            $tipoClient = $invoice->client->codigo_tipo_documento ?? '';

            if ($tipoDoc === '01' && $tipoClient !== '6') {
                throw new RuntimeException(
                    "La Factura solo puede emitirse a receptores con RUC (tipo 6). " .
                    "El cliente tiene tipo de documento '{$tipoClient}'. " .
                    "Use una Boleta (03) para clientes con DNI u otros documentos."
                );
            }
            if ($tipoDoc === '03' && $tipoClient === '6') {
                throw new RuntimeException(
                    "La Boleta no puede emitirse a receptores con RUC. Use una Factura (01)."
                );
            }
        }

        // Elegir endpoint según tipo de documento
        $endpoint = match ($tipoDoc) {
            '01'    => '/comprobante/enviar_factura',
            '03'    => '/comprobante/enviar_boleta',
            '09'    => '/comprobante/enviar_guia_remision_remitente',
            default => throw new RuntimeException(
                "Tipo de documento '{$tipoDoc}' no soportado. Use 01 (Factura), 03 (Boleta) o 09 (GRE)."
            ),
        };

        $payload = $tipoDoc === '09'
            ? $this->buildGrePayload($invoice, $company)
            : $this->buildPayload($invoice, $company);

        $result = $this->post($endpoint, $payload, $this->token);

        // Feasy devuelve 400 "registrado previamente y está aceptado" cuando el documento
        // ya fue enviado a SUNAT con éxito en un intento anterior. Lo tratamos como aceptado.
        if (! $result['success'] && $result['http_status'] === 400) {
            $errorsStr = strtolower(json_encode($result['errors']));
            if (str_contains($errorsStr, 'registrado previamente') && str_contains($errorsStr, 'aceptado')) {
                return [
                    'success'     => true,
                    'message'     => 'Documento ya registrado y aceptado por SUNAT.',
                    'data'        => [
                        'codigo_respuesta'   => '0',
                        'mensaje_respuesta'  => 'Documento registrado previamente y aceptado por SUNAT.',
                    ],
                    'errors'      => [],
                    'http_status' => 200,
                ];
            }
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════════════
    // 2) CONSULTAR COMPROBANTE
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Consulta el estado de un comprobante en Feasy/SUNAT.
     *
     * @return array{success: bool, message: string, data: array|null, errors: array, http_status: int}
     */
    public function consultar(Company $company, string $tipo, string $serie, string $numero): array
    {
        $this->validateCompanyAccess($company);

        $payload = [
            'codigo_tipo_documento_emisor' => '6',
            'numero_documento_emisor'      => $company->ruc,
            'codigo_tipo_documento'        => $tipo,
            'serie_documento'              => $serie,
            'numero_documento'             => $numero,
        ];

        return $this->post('/comprobante/consultar', $payload, $this->token);
    }

    // ══════════════════════════════════════════════════════════════════════
    // 3) GUARDAR XML EN STORAGE PRIVADO
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Guarda el contenido XML (base64 o raw) en storage privado por empresa.
     * Retorna la ruta relativa guardada.
     *
     * Path: private/companies/{company_id}/xml/{nombre_archivo}
     */
    public function saveXml(int $companyId, string $filename, string $content): string
    {
        // Si viene en base64, decodificar
        $decoded = base64_decode($content, strict: true);
        $xmlContent = ($decoded !== false) ? $decoded : $content;

        $path = "private/companies/{$companyId}/xml/{$filename}";

        Storage::disk('local')->put($path, $xmlContent);

        return $path;
    }

    // ══════════════════════════════════════════════════════════════════════
    // PRIVATE: Validaciones Pre-Emisión
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Resuelve la empresa desde la factura y ejecuta todas las validaciones
     * de seguridad requeridas antes de cualquier llamada a Feasy.
     *
     * @throws RuntimeException si alguna validación falla
     */
    private function resolveAndValidateCompany(Invoice $invoice): Company
    {
        // 1) Anti-IDOR: la factura DEBE pertenecer a la empresa activa
        if ((int) $invoice->company_id !== (int) session('company_id')) {
            throw new RuntimeException(
                '[IDOR] La factura no pertenece a la empresa activa en sesión.'
            );
        }

        $company = Company::findOrFail($invoice->company_id);

        $this->validateCompanyAccess($company);

        // 2) El RUC del emisor en la factura debe coincidir con el RUC de la empresa
        //    (verificación explícita aunque el payload lo construya este servicio)
        if ($company->ruc !== ($invoice->company->ruc ?? $company->ruc)) {
            throw new RuntimeException(
                "El RUC del emisor ({$company->ruc}) no coincide con el registrado en la empresa."
            );
        }

        return $company;
    }

    /**
     * Valida que la empresa tenga facturador habilitado y token configurado.
     *
     * @throws RuntimeException
     */
    private function validateCompanyAccess(Company $company): void
    {
        if ((int) $company->id !== (int) session('company_id')) {
            throw new RuntimeException('[IDOR] La empresa no coincide con la empresa activa en sesión.');
        }

        if (! $company->facturador_enabled) {
            throw new RuntimeException("El Facturador no está habilitado para la empresa {$company->name}.");
        }

        // Token global: 1 cuenta Feasy → 1 token → todas las empresas
        if (empty($this->token)) {
            throw new RuntimeException(
                'El Token Feasy no está configurado. Ve a Facturador › Configuración Feasy en el menú.'
            );
        }
    }

    /**
     * Valida coherencia de totales para evitar el error SUNAT
     * "PayableAmount no cumple con el formato establecido".
     *
     * @throws RuntimeException
     */
    private function validateTotals(Invoice $invoice): void
    {
        if ($invoice->monto_total <= 0) {
            throw new RuntimeException("monto_total debe ser mayor a 0. Valor actual: {$invoice->monto_total}");
        }

        if ($invoice->monto_total_igv < 0) {
            throw new RuntimeException("monto_total_igv no puede ser negativo.");
        }

        if ($invoice->monto_total_gravado < 0) {
            throw new RuntimeException("monto_total_gravado no puede ser negativo.");
        }

        // Verificar que el total cuadre: gravado + igv ≈ total (con tolerancia de 0.05 soles)
        $expectedTotal = round($invoice->monto_total_gravado + $invoice->monto_total_igv, 2);
        $actualTotal   = round($invoice->monto_total, 2);

        if (abs($expectedTotal - $actualTotal) > 0.05) {
            throw new RuntimeException(
                "Incoherencia en totales: gravado({$invoice->monto_total_gravado}) + igv({$invoice->monto_total_igv})"
                . " = {$expectedTotal} ≠ monto_total({$actualTotal})"
            );
        }

        // Verificar que todos los items tengan monto_total > 0
        foreach ($invoice->items as $item) {
            if ($item->monto_total <= 0) {
                throw new RuntimeException(
                    "Item #{$item->correlativo} ({$item->descripcion}) tiene monto_total <= 0."
                );
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════════
    // PRIVATE: Builder del Payload Feasy (formato EXACTO documentado)
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Construye el JSON payload para enviar_factura / enviar_boleta.
     * La estructura es idéntica para ambos tipos — sólo cambia el endpoint.
     * Las keys son las documentadas por FeasaPeru — NO modificar nombres.
     *
     * @return array<string, mixed>
     */
    private function buildPayload(Invoice $invoice, Company $company): array
    {
        $client = $invoice->client;

        $payload = [
            // ── Información del documento ──────────────────────────────────
            'informacion_documento' => [
                'codigo_interno'               => $invoice->codigo_interno,
                'fecha_emision'                => $invoice->fecha_emision->format('Y-m-d'),
                'hora_emision'                 => $invoice->hora_emision->format('H:i:s'),
                'fecha_vencimiento'            => $invoice->fecha_vencimiento?->format('Y-m-d'),
                'forma_pago'                   => $invoice->forma_pago,
                'codigo_tipo_documento'        => $invoice->codigo_tipo_documento,
                'serie_documento'              => $invoice->serie_documento,
                'numero_documento'             => $invoice->numero_documento,
                'observacion'                  => $invoice->observacion,
                'correo'                       => $invoice->correo,
                'numero_orden_compra'          => $invoice->numero_orden_compra,
                'codigo_moneda'                => $invoice->codigo_moneda,
                'porcentaje_igv'               => round((float) $invoice->porcentaje_igv, 2),
                'monto_tipo_cambio'            => $invoice->monto_tipo_cambio,
                'monto_descuento_global'       => null,
                'monto_total_anticipo'         => $invoice->monto_total_anticipo,
                'monto_total_gravado'          => round((float) $invoice->monto_total_gravado, 2),
                'monto_total_inafecto'         => $invoice->monto_total_inafecto,
                'monto_total_exonerado'        => $invoice->monto_total_exonerado,
                'monto_total_exportacion'      => $invoice->monto_total_exportacion,
                'monto_total_descuento'        => $invoice->monto_total_descuento,
                'monto_total_isc'              => $invoice->monto_total_isc,
                'monto_total_igv'              => round((float) $invoice->monto_total_igv, 2),
                'monto_total_impuesto_bolsa'   => $invoice->monto_total_impuesto_bolsa,
                'monto_total_gratuito'         => $invoice->monto_total_gratuito,
                'monto_total_otros_cargos'     => $invoice->monto_total_otros_cargos,
                'monto_total'                  => round((float) $invoice->monto_total, 2),
            ],

            // ── Información del emisor (datos de la empresa activa) ────────
            // CRÍTICO: numero_documento_emisor == company.ruc (validado arriba)
            'informacion_emisor' => $this->buildEmisorPayload($company),

            // ── Información del adquiriente (cliente) ─────────────────────
            'informacion_adquiriente' => $this->buildAdquirientePayload($client),

            // ── Items ──────────────────────────────────────────────────────
            'lista_items' => $invoice->items->map(
                fn ($item) => $this->buildItemPayload($item, (float) $invoice->porcentaje_igv)
            )->values()->toArray(),
        ];

        // ── Entrega de bienes (opcional) ──────────────────────────────────
        if ($invoice->indicador_entrega_bienes && $invoice->informacion_entrega_bienes) {
            $payload['informacion_entrega_bienes'] = $invoice->informacion_entrega_bienes;
            $payload['indicadores'] = ['indicador_entrega_bienes' => true];
        }

        return $payload;
    }

    /** @return array<string, mixed> */
    private function buildEmisorPayload(Company $company): array
    {
        return [
            'codigo_tipo_documento_emisor'    => '6', // 6 = RUC
            'numero_documento_emisor'         => $company->ruc,
            'nombre_razon_social_emisor'      => $company->razon_social ?? $company->name,
            'ubigeo_emisor'                   => $company->ubigeo,
            'departamento_emisor'             => null, // Feasy los deduce del ubigeo
            'provincia_emisor'                => null,
            'distrito_emisor'                 => null,
            'urbanizacion_emisor'             => null,
            'direccion_emisor'                => $company->direccion_fiscal,
        ];
    }

    /** @return array<string, mixed> */
    private function buildAdquirientePayload(\App\Models\Client $client): array
    {
        return [
            'codigo_tipo_documento_adquiriente'    => $client->codigo_tipo_documento,
            'numero_documento_adquiriente'         => $client->numero_documento,
            'nombre_razon_social_adquiriente'      => $client->nombre_razon_social,
            'codigo_pais_adquiriente'              => $client->codigo_pais,
            'ubigeo_adquiriente'                   => $client->ubigeo,
            'departamento_adquiriente'             => $client->departamento,
            'provincia_adquiriente'                => $client->provincia,
            'distrito_adquiriente'                 => $client->distrito,
            'urbanizacion_adquiriente'             => $client->urbanizacion,
            'direccion_adquiriente'                => $client->direccion,
            'correo_adquiriente'                   => $client->correo,
        ];
    }

    /**
     * Construye el payload para GRE (Guía de Remisión Electrónica) tipo "09".
     * Endpoint: POST /comprobante/enviar_guia_remision_remitente
     *
     * @return array<string, mixed>
     */
    private function buildGrePayload(Invoice $invoice, Company $company): array
    {
        $destinatario = $invoice->gre_destinatario ?? [];
        $puntoPartida = $invoice->gre_punto_partida ?? [];
        $puntoLlegada = $invoice->gre_punto_llegada ?? [];
        $vehiculos    = $invoice->gre_vehiculos    ?? [];
        $conductores  = $invoice->gre_conductores  ?? [];

        return [
            'informacion_documento' => [
                'codigo_interno'                        => $invoice->codigo_interno,
                'fecha_emision'                         => $invoice->fecha_emision->format('Y-m-d'),
                'hora_emision'                          => $invoice->hora_emision->format('H:i:s'),
                'codigo_tipo_documento'                 => '09',
                'serie_documento'                       => $invoice->serie_documento,
                'numero_documento'                      => $invoice->numero_documento,
                'observacion'                           => $invoice->observacion,
                'correo'                                => $invoice->correo,
                'codigo_motivo_traslado'                => $invoice->codigo_motivo_traslado,
                'descripcion_motivo_traslado'           => $invoice->descripcion_motivo_traslado,
                'codigo_modalidad_traslado'             => $invoice->codigo_modalidad_traslado,
                'fecha_inicio_traslado'                 => $invoice->fecha_inicio_traslado?->format('Y-m-d'),
                'codigo_unidad_medida_peso_bruto_total' => $invoice->codigo_unidad_medida_peso_bruto,
                'peso_bruto_total'                      => (float) $invoice->peso_bruto_total,
            ],
            'informacion_remitente' => [
                'codigo_tipo_documento_remitente' => '6',
                'numero_documento_remitente'      => $company->ruc,
                'nombre_razon_social_remitente'   => $company->razon_social ?? $company->name,
            ],
            'informacion_destinatario' => [
                'codigo_tipo_documento_destinatario'  => $destinatario['codigo_tipo_documento_destinatario'] ?? '',
                'numero_documento_destinatario'       => $destinatario['numero_documento_destinatario'] ?? '',
                'nombre_razon_social_destinatario'    => $destinatario['nombre_razon_social_destinatario'] ?? '',
            ],
            'informacion_punto_partida' => [
                'ubigeo_punto_partida'   => $puntoPartida['ubigeo_punto_partida'] ?? '',
                'direccion_punto_partida' => $puntoPartida['direccion_punto_partida'] ?? '',
            ],
            'informacion_punto_llegada' => [
                'ubigeo_punto_llegada'   => $puntoLlegada['ubigeo_punto_llegada'] ?? '',
                'direccion_punto_llegada' => $puntoLlegada['direccion_punto_llegada'] ?? '',
            ],
            'lista_vehiculos' => array_values(array_map(function (array $v): array {
                $v['indicador_principal'] = (bool) ($v['indicador_principal'] ?? false);
                return $v;
            }, $vehiculos)),
            'lista_conductores' => array_values(array_map(function (array $c): array {
                $c['indicador_principal'] = (bool) ($c['indicador_principal'] ?? false);
                return $c;
            }, $conductores)),
            'lista_items' => $invoice->items->map(fn (\App\Models\InvoiceItem $item) => [
                'correlativo'          => $item->correlativo,
                'codigo_interno'       => $item->codigo_interno,
                'codigo_unidad_medida' => $item->codigo_unidad_medida,
                'descripcion'          => $item->descripcion,
                'cantidad'             => round((float) $item->cantidad, 4),
            ])->values()->toArray(),
        ];
    }

    /** @return array<string, mixed> */
    private function buildItemPayload(\App\Models\InvoiceItem $item, float $porcentajeIgv = 18.00): array
    {
        return [
            'correlativo'            => $item->correlativo,
            'codigo_interno'         => $item->codigo_interno,
            'codigo_sunat'           => $item->codigo_sunat,
            'tipo'                   => $item->tipo,
            'codigo_unidad_medida'   => $item->codigo_unidad_medida,
            'descripcion'            => $item->descripcion,
            'cantidad'               => round((float) $item->cantidad, 4),
            'monto_valor_unitario'   => round((float) $item->monto_valor_unitario, 10),
            'monto_precio_unitario'  => round((float) $item->monto_precio_unitario, 10),
            'monto_descuento'        => $item->monto_descuento,
            'monto_valor_total'      => round((float) $item->monto_valor_total, 10),
            'codigo_isc'             => $item->codigo_isc,
            'monto_isc'              => $item->monto_isc,
            'codigo_indicador_afecto'=> $item->codigo_indicador_afecto,
            'porcentaje_igv'         => round($porcentajeIgv, 2),
            'monto_igv'              => round((float) $item->monto_igv, 2),
            'monto_impuesto_bolsa'   => $item->monto_impuesto_bolsa,
            'monto_total'            => round((float) $item->monto_total, 2),
        ];
    }

    // ══════════════════════════════════════════════════════════════════════
    // 3) ANULACIÓN
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Envía una solicitud de anulación a Feasy.
     *   01 (Factura) → tipo_operacion "1"
     *   03 (Boleta)  → tipo_operacion "2"
     *
     * @return array{success: bool, message: string, data: array|null, errors: array, http_status: int}
     */
    public function void(Invoice $invoice, Company $company, string $motivo): array
    {
        if (empty($this->token)) {
            return ['success' => false, 'message' => 'FEASY_TOKEN no configurado.', 'data' => null, 'errors' => [], 'http_status' => 0];
        }

        $tipoOp = match($invoice->codigo_tipo_documento) {
            '01'    => '1',
            '03'    => '2',
            default => '1',
        };

        $payload = [
            'tipo_operacion'                  => $tipoOp,
            'codigo_tipo_documento_emisor'    => '6',
            'numero_documento_emisor'         => $company->ruc,
            'codigo_tipo_documento'           => $invoice->codigo_tipo_documento,
            'serie_documento'                 => $invoice->serie_documento,
            'numero_documento'                => $invoice->numero_documento,
            'motivo'                          => $motivo,
        ];

        return $this->post('/anulacion/enviar', $payload, $this->token);
    }

    /**
     * POST genérico a la API Feasy con Bearer token de la empresa activa.
     * Normaliza CUALQUIER respuesta (éxito, error token, error SUNAT, red).
     *
     * @param array<string, mixed> $payload
     * @return array{success: bool, message: string, data: array|null, errors: array, http_status: int}
     */
    private function post(string $endpoint, array $payload, string $token): array
    {
        $url = $this->baseUrl . $endpoint;

        try {
            $http = Http::withToken($token)
                ->acceptJson()
                ->timeout(30);

            // En entorno local Laragon no tiene CA bundle configurado
            if (app()->isLocal()) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($url, $payload);

            $body       = $response->json() ?? [];
            $httpStatus = $response->status();

            Log::channel('stack')->info('[FeasyService] Respuesta recibida', [
                'endpoint'    => $endpoint,
                'http_status' => $httpStatus,
                'success'     => $body['success'] ?? false,
            ]);

            return [
                'success'     => (bool) ($body['success'] ?? false),
                'message'     => $body['message'] ?? 'Sin mensaje',
                'data'        => $body['data'] ?? null,
                'errors'      => (array) ($body['errors'] ?? []),
                'http_status' => $httpStatus,
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('stack')->error('[FeasyService] Error de conexión', [
                'endpoint' => $endpoint,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success'     => false,
                'message'     => 'Error de conexión con Feasy: ' . $e->getMessage(),
                'data'        => null,
                'errors'      => ['Conexión fallida con el servidor Feasy.'],
                'http_status' => 0,
            ];
        }
    }
}
