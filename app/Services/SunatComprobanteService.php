<?php

namespace App\Services;

use App\Models\SunatApiCredential;
use App\Models\SunatComprobanteValidacion;
use App\Models\SunatToken;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class SunatComprobanteService
{
    public function getCredentialForEmpresa(int $empresaId): SunatApiCredential
    {
        $credential = SunatApiCredential::where('empresa_id', $empresaId)
            ->where('is_active', true)
            ->first();

        if (! $credential) {
            throw new RuntimeException('La empresa seleccionada no tiene credenciales SUNAT API activas.');
        }

        return $credential;
    }

    public function getToken(int $empresaId): SunatToken
    {
        $credential = $this->getCredentialForEmpresa($empresaId);
        $token = SunatToken::where('empresa_id', $empresaId)
            ->where('sunat_api_credential_id', $credential->id)
            ->latest('expires_at')
            ->first();

        if ($this->tokenIsValid($token)) {
            return $token;
        }

        return $this->refreshToken($credential);
    }

    public function tokenIsValid(?SunatToken $token): bool
    {
        return $token !== null
            && $token->access_token
            && $token->expires_at?->gt(now()->addMinutes(2));
    }

    public function refreshToken(SunatApiCredential $credential): SunatToken
    {
        $url = str_replace('{client_id}', $credential->client_id, $credential->token_url ?: config('sunat.token_url'));

        try {
            $response = Http::timeout((int) config('sunat.timeout', 25))
                ->asForm()
                ->post($url, [
                    'grant_type' => 'client_credentials',
                    'scope' => $credential->scope ?: config('sunat.scope'),
                    'client_id' => $credential->client_id,
                    'client_secret' => $credential->client_secret,
                ]);
        } catch (ConnectionException $e) {
            $this->recordCredentialError($credential, 'Timeout o error de conexión al generar token SUNAT.', $e);
            throw new RuntimeException('SUNAT no respondió al generar el token. Intenta nuevamente.');
        }

        if (! $response->ok()) {
            $responseJson = $response->json();
            $sunatError = is_array($responseJson) ? (string) ($responseJson['error'] ?? '') : '';
            $sunatDescription = is_array($responseJson) ? (string) ($responseJson['error_description'] ?? '') : '';

            $message = $this->tokenErrorMessage($response->status(), $sunatError, $sunatDescription);

            $this->recordCredentialError($credential, $message, null, [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 1000),
            ]);

            throw new RuntimeException($message);
        }

        $json = $response->json();

        if (empty($json['access_token'])) {
            $this->recordCredentialError($credential, 'SUNAT respondió sin access_token.', null, [
                'body' => $json,
            ]);

            throw new RuntimeException('SUNAT respondió sin token de acceso.');
        }

        return DB::transaction(function () use ($credential, $json): SunatToken {
            $expiresIn = (int) ($json['expires_in'] ?? 3600);

            $token = SunatToken::create([
                'empresa_id' => $credential->empresa_id,
                'sunat_api_credential_id' => $credential->id,
                'access_token' => (string) $json['access_token'],
                'token_type' => (string) ($json['token_type'] ?? 'Bearer'),
                'expires_in' => $expiresIn,
                'generated_at' => now(),
                'expires_at' => now()->addSeconds($expiresIn),
            ]);

            $credential->forceFill([
                'last_token_generated_at' => now(),
                'last_error' => null,
            ])->save();

            return $token;
        });
    }

    public function validarComprobante(int $empresaId, array $data): SunatComprobanteValidacion
    {
        $credential = $this->getCredentialForEmpresa($empresaId);
        $token = $this->getToken($empresaId);

        if ((int) $token->empresa_id !== $empresaId) {
            Log::critical('SUNAT token pertenece a otra empresa', [
                'empresa_id' => $empresaId,
                'token_empresa_id' => $token->empresa_id,
                'token_id' => $token->id,
            ]);
            throw new RuntimeException('El token SUNAT no pertenece a la empresa seleccionada.');
        }

        $payload = [
            'numRuc' => preg_replace('/\D/', '', (string) $data['numRuc']),
            'codComp' => (string) $data['codComp'],
            'numeroSerie' => strtoupper(trim((string) $data['numeroSerie'])),
            'numero' => (int) $data['numero'],
            'fechaEmision' => (string) $data['fechaEmision'],
        ];

        if (array_key_exists('monto', $data) && $data['monto'] !== null && $data['monto'] !== '') {
            $payload['monto'] = round((float) $data['monto'], 2);
        }

        $url = str_replace('{ruc}', $credential->ruc_consultante, $credential->consulta_url ?: config('sunat.consulta_url'));

        try {
            $response = Http::timeout((int) config('sunat.timeout', 25))
                ->withToken((string) $token->access_token)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);
        } catch (ConnectionException $e) {
            Log::warning('SUNAT consulta timeout', [
                'empresa_id' => $empresaId,
                'ruc_consultante' => $credential->ruc_consultante,
                'error' => $e->getMessage(),
            ]);

            $responseData = [
                'success' => false,
                'message' => 'SUNAT no respondió a tiempo.',
                'errorCode' => 'TIMEOUT',
            ];

            return $this->guardarHistorial($empresaId, $payload, $responseData);
        }

        $responseData = $response->json();

        if (! is_array($responseData)) {
            $responseData = [
                'success' => false,
                'message' => 'SUNAT respondió con un formato no válido.',
                'errorCode' => (string) $response->status(),
                'raw' => Str::limit($response->body(), 1000),
            ];
        }

        if (! $response->ok()) {
            $responseData['success'] = false;
            $responseData['message'] = $responseData['message'] ?? $this->httpErrorMessage($response->status());
            $responseData['errorCode'] = $responseData['errorCode'] ?? (string) $response->status();

            if ($response->status() === 401) {
                SunatToken::whereKey($token->id)->delete();
            }
        }

        return $this->guardarHistorial($empresaId, $payload, $responseData);
    }

    public function guardarHistorial(int $empresaId, array $requestData, array $responseData): SunatComprobanteValidacion
    {
        $credential = $this->getCredentialForEmpresa($empresaId);
        $data = $responseData['data'] ?? [];
        $fecha = Carbon::createFromFormat('d/m/Y', $requestData['fechaEmision'])->startOfDay();

        return SunatComprobanteValidacion::create([
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'sunat_api_credential_id' => $credential->id,
            'ruc_consultante' => $credential->ruc_consultante,
            'num_ruc_emisor' => $requestData['numRuc'],
            'cod_comp' => $requestData['codComp'],
            'numero_serie' => $requestData['numeroSerie'],
            'numero' => $requestData['numero'],
            'fecha_emision' => $fecha,
            'monto' => $requestData['monto'] ?? null,
            'success' => (bool) ($responseData['success'] ?? false),
            'message' => $responseData['message'] ?? null,
            'estado_cp' => $data['estadoCp'] ?? null,
            'estado_cp_texto' => $this->mapEstadoCp($data['estadoCp'] ?? null),
            'estado_ruc' => $data['estadoRuc'] ?? null,
            'estado_ruc_texto' => $this->mapEstadoRuc($data['estadoRuc'] ?? null),
            'cond_domi_ruc' => $data['condDomiRuc'] ?? null,
            'cond_domi_ruc_texto' => $this->mapCondicionDomicilio($data['condDomiRuc'] ?? null),
            'observaciones' => $data['observaciones'] ?? [],
            'error_code' => $responseData['errorCode'] ?? null,
            'request_payload' => $requestData,
            'response_payload' => $responseData,
        ]);
    }

    public function mapEstadoCp($codigo): string
    {
        return [
            '0' => 'NO EXISTE',
            '1' => 'ACEPTADO',
            '2' => 'ANULADO',
            '3' => 'AUTORIZADO',
            '4' => 'NO AUTORIZADO',
        ][(string) $codigo] ?? 'DESCONOCIDO';
    }

    public function mapEstadoRuc($codigo): string
    {
        return [
            '00' => 'ACTIVO',
            '01' => 'BAJA PROVISIONAL',
            '02' => 'BAJA PROV. POR OFICIO',
            '03' => 'SUSPENSION TEMPORAL',
            '10' => 'BAJA DEFINITIVA',
            '11' => 'BAJA DE OFICIO',
            '22' => 'INHABILITADO - VENT. UNICA',
        ][(string) $codigo] ?? 'DESCONOCIDO';
    }

    public function mapCondicionDomicilio($codigo): string
    {
        return [
            '00' => 'HABIDO',
            '09' => 'PENDIENTE',
            '11' => 'POR VERIFICAR',
            '12' => 'NO HABIDO',
            '20' => 'NO HALLADO',
        ][(string) $codigo] ?? 'DESCONOCIDO';
    }

    public function mapTipoComprobante($codigo): string
    {
        return [
            '01' => 'FACTURA',
            '03' => 'BOLETA DE VENTA',
            '04' => 'LIQUIDACION DE COMPRA',
            '07' => 'NOTA DE CREDITO',
            '08' => 'NOTA DE DEBITO',
            'R1' => 'RECIBO POR HONORARIOS',
            'R7' => 'NOTA DE CREDITO DE RECIBOS',
        ][(string) $codigo] ?? 'COMPROBANTE';
    }

    private function httpErrorMessage(int $status): string
    {
        return match ($status) {
            400 => 'SUNAT rechazó los datos enviados. Revisa formato, fecha, serie, número y monto.',
            401 => 'SUNAT no autorizó la consulta. Se debe renovar el token o revisar credenciales.',
            500 => 'SUNAT presentó un error interno.',
            default => 'No se pudo completar la consulta SUNAT.',
        };
    }

    private function tokenErrorMessage(int $status, string $sunatError = '', string $sunatDescription = ''): string
    {
        $detail = trim($sunatDescription ?: $sunatError);

        if ($sunatError === 'unauthorized_client' || str_contains(strtolower($detail), 'cliente no autorizado')) {
            return 'SUNAT respondió "cliente no autorizado". Revisa que el client_id y client_secret correspondan al RUC consultante, que la credencial API esté activa en Menú SOL y que tenga permiso para Consulta Integrada de Comprobante de Pago.';
        }

        return match ($status) {
            400 => 'SUNAT rechazó la solicitud de token. Revisa client_id, client_secret y scope.',
            401 => 'SUNAT rechazó las credenciales configuradas.',
            default => 'SUNAT no pudo generar el token en este momento.'.($detail ? " Detalle SUNAT: {$detail}." : ''),
        };
    }

    private function recordCredentialError(SunatApiCredential $credential, string $message, ?\Throwable $e = null, array $context = []): void
    {
        $credential->forceFill(['last_error' => $message])->save();

        Log::warning('SUNAT API credential error', array_merge([
            'empresa_id' => $credential->empresa_id,
            'credential_id' => $credential->id,
            'message' => $message,
            'exception' => $e?->getMessage(),
        ], $context));
    }
}
