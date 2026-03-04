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

        if (empty($client->usuario_sol) || empty($client->clave_sol)) {
            return response()->json([
                'ok'    => false,
                'error' => "El cliente no tiene credenciales SOL configuradas.",
            ], 422);
        }

        $baseUrl = rtrim(config('services.bot_cookies.url', 'http://localhost:8001'), '/');
        $apiKey  = config('services.bot_cookies.key', '');

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'                    => $apiKey,
                    'ngrok-skip-browser-warning'   => 'true',
                    'User-Agent'                   => 'LaravelBot/1.0',
                    'Accept'                       => 'application/json',
                ])
                ->post("{$baseUrl}/proxy/create", [
                    'ruc'         => $client->numero_documento,
                    'usuario_sol' => $client->usuario_sol,
                    // El cast encrypted del modelo ya devuelve la clave desencriptada.
                    'clave_sol'   => $client->clave_sol,
                    'portal'      => 'sunat',
                ]);

            $data = $response->json();

            // Si bot_cookies devuelve HTML (p.ej. advertencia ngrok), exponer detalle claro.
            if (! is_array($data)) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Respuesta no valida del microservicio bot_cookies. Verifique BOT_COOKIES_URL y headers ngrok.',
                ], 502);
            }

            if (! ($data['ok'] ?? false)) {
                return response()->json([
                    'ok'    => false,
                    'error' => $data['detalle'] ?? $data['error'] ?? 'Error al conectar con SUNAT.',
                ], $response->status() >= 400 ? $response->status() : 502);
            }

            return response()->json([
                'ok'        => true,
                'proxy_url' => $data['proxy_url'],
                'ruc'       => $data['ruc'] ?? $client->numero_documento,
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException) {
            return response()->json([
                'ok'    => false,
                'error' => 'No se pudo conectar con el servicio de autenticación. Verifique que esté activo.',
            ], 503);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'    => false,
                'error' => 'Error inesperado: ' . $e->getMessage(),
            ], 500);
        }
    }
}
