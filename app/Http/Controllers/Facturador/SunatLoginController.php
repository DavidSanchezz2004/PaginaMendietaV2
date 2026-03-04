<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Autenticación automática en SUNAT.
 *
 * Flujos disponibles:
 *   A) GET  /facturador/clients/{client}/sunat-login  → formulario autosubmit (fallback)
 *   B) POST /facturador/clients/{client}/sunat-proxy  → devuelve proxy_url para iframe modal
 */
class SunatLoginController extends Controller
{
    /**
     * Renderiza la página de autosubmit hacia el login de SUNAT.
     * GET /facturador/clients/{client}/sunat-login
     */
    public function redirect(Client $client): View|RedirectResponse
    {
        $this->authorize('update', $client);

        if (empty($client->usuario_sol) || empty($client->clave_sol)) {
            return back()->with(
                'error',
                "El cliente «{$client->nombre_razon_social}» no tiene credenciales SOL configuradas. Edítalo y agrega el Usuario y Clave SOL."
            );
        }

        // clave_sol se desencripta automáticamente por el cast 'encrypted' del modelo
        return view('facturador.clients.sunat-autosubmit', [
            'ruc'         => $client->numero_documento,
            'usuario_sol' => strtoupper($client->usuario_sol),
            'clave_sol'   => $client->clave_sol,
            'nombre'      => $client->nombre_razon_social,
        ]);
    }

    /**
     * Llama al microservicio bot_cookies v2 (POST /proxy/create) y devuelve
     * la proxy_url para ser cargada en el iframe del modal.
     * POST /facturador/clients/{client}/sunat-proxy
     */
    public function getProxyUrl(Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        try {
            $botUrl = config('services.bot_cookies.url');
            $botKey = config('services.bot_cookies.key');

            // Debug: verificar que las variables de configuración están presentes.
            if (! $botUrl || ! $botKey) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Bot config missing: url=' . $botUrl . ' key=' . ($botKey ? 'set' : 'empty'),
                ], 500);
            }

            if (empty($client->usuario_sol) || empty($client->clave_sol)) {
                return response()->json([
                    'ok'    => false,
                    'error' => "El cliente no tiene credenciales SOL configuradas.",
                ], 422);
            }

            $botUrl = rtrim($botUrl, '/');

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'                  => $botKey,
                    'ngrok-skip-browser-warning' => 'true',
                    'User-Agent'                 => 'LaravelBot/1.0',
                    'Accept'                     => 'application/json',
                ])
                ->post("{$botUrl}/proxy/create", [
                    'ruc'         => $client->numero_documento,
                    'usuario_sol' => $client->usuario_sol,
                    // El cast encrypted del modelo ya devuelve la clave desencriptada.
                    'clave_sol'   => $client->clave_sol,
                    'portal'      => 'sunat',
                ]);

            \Log::info('Bot response status: ' . $response->status());
            \Log::info('Bot response body: ' . $response->body());

            $data = $response->json();

            if (! ($data['ok'] ?? false)) {
                return response()->json([
                    'ok'         => false,
                    'error'      => $data['detalle'] ?? $data['error'] ?? 'Error del bot',
                    'bot_status' => $response->status(),
                    'bot_body'   => $response->body(),
                ], 500);
            }

            return response()->json([
                'ok'        => true,
                'proxy_url' => route('facturador.clients.sunat-frame', ['token' => $data['token']]),
                'ruc'       => $data['ruc'] ?? $client->numero_documento,
            ]);

        } catch (\Exception $e) {
            \Log::error('SunatProxy error: ' . $e->getMessage());
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }
}
