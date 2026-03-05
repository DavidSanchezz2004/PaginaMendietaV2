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
 *   A) GET  /facturador/clients/{client}/sunat-login   → formulario autosubmit (fallback)
 *   B) GET  /facturador/clients/{client}/abrir-sunat   → devuelve URL para extensión Chrome
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
     * Llama a POST /proxy/create → devuelve { ok, url } con la URL ext-inject.
     * El bot responde inmediatamente con el token; la extensión Chrome espera
     * el login y redirige a SUNAT por su cuenta.
     * GET /facturador/clients/{client}/abrir-sunat
     */
    public function abrirSunat(Client $client): JsonResponse
    {
        $this->authorize('update', $client);

        try {
            $botUrl = rtrim(config('services.sunat_bot.url'), '/');
            $botKey = config('services.sunat_bot.key');

            if (! $botUrl || ! $botKey) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'SUNAT bot config missing (SUNAT_BOT_URL / SUNAT_API_KEY)',
                ], 500);
            }

            if (empty($client->usuario_sol) || empty($client->clave_sol)) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'El cliente no tiene credenciales SOL configuradas.',
                ], 422);
            }

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'  => $botKey,
                    'User-Agent' => 'LaravelBot/1.0',
                    'Accept'     => 'application/json',
                ])
                ->post("{$botUrl}/proxy/create", [
                    'ruc'         => $client->numero_documento,
                    'usuario_sol' => $client->usuario_sol,
                    'clave_sol'   => $client->clave_sol,
                    'portal'      => 'sunat',
                ]);

            $data = $response->json();

            if (! ($data['ok'] ?? false)) {
                return response()->json([
                    'ok'    => false,
                    'error' => $data['detalle'] ?? $data['error'] ?? 'Error del bot',
                ], 500);
            }

            $token = $data['token'];

            $client->update([
                'sunat_token'            => $token,
                'sunat_token_expires_at' => now()->addMinutes(120),
            ]);

            return response()->json([
                'ok'  => true,
                'url' => "{$botUrl}/ext-inject/{$token}",
            ]);

        } catch (\Exception $e) {
            \Log::error('SunatBot error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
