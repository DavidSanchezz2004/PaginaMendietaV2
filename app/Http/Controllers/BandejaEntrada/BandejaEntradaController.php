<?php

namespace App\Http\Controllers\BandejaEntrada;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Enums\RoleEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BandejaEntradaController extends Controller
{
    private function botUrl(): string
    {
        return rtrim((string) config('services.sunat_bot.url'), '/');
    }

    private function botKey(): string
    {
        return (string) config('services.sunat_bot.key');
    }

    /**
     * Vista principal — lista de empresas con botón "Buscar Buzón".
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $role = $user->role instanceof RoleEnum
            ? $user->role->value
            : (string) $user->role;

        if (in_array($role, ['admin', 'supervisor'], true)) {
            $companies = Company::orderBy('name')->get();
        } else {
            $companies = $user->companies()
                ->wherePivot('status', 'active')
                ->orderBy('name')
                ->get();
        }

        $q = (string) $request->query('q', '');
        if ($q !== '') {
            $companies = $companies->filter(
                fn ($c) => str_contains(mb_strtolower($c->name), mb_strtolower($q))
            );
        }

        return view('bandeja-sunat.index', [
            'companies' => $companies->values(),
            'filters'   => ['q' => $q],
        ]);
    }

    /**
     * Inicia el login del buzón y guarda el token en caché (110 min).
     * POST /bandeja-sunat/iniciar/{company}
     */
    public function iniciar(Request $request, Company $company): JsonResponse
    {
        abort_if(! $request->user(), 403);

        if (! $company->canUseSunatPortal()) {
            return response()->json(['ok' => false, 'error' => 'Empresa inactiva.'], 422);
        }

        if (! $company->hasSunatCredentials()) {
            return response()->json(['ok' => false, 'error' => 'Sin credenciales SOL.'], 422);
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'x-api-key' => $this->botKey(),
                    'Accept'    => 'application/json',
                ])
                ->post("{$this->botUrl()}/buzon/create", [
                    'ruc'         => $company->ruc,
                    'usuario_sol' => $company->usuario_sol,
                    'clave_sol'   => $company->clave_sol,
                    'portal'      => 'buzon',
                ]);

            $data = $response->json();

            if (! ($data['ok'] ?? false)) {
                return response()->json([
                    'ok'    => false,
                    'error' => $data['detalle'] ?? $data['error'] ?? 'Error del bot.',
                ], 500);
            }

            Cache::put(
                "buzon_token_{$company->id}",
                $data['token'],
                now()->addMinutes(110)
            );

            Log::info('BandejaEntrada::iniciar OK', [
                'company' => $company->id,
                'ruc'     => $company->ruc,
            ]);

            return response()->json(['ok' => true, 'token' => $data['token']]);

        } catch (\Exception $e) {
            Log::error('BandejaEntrada::iniciar error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista mensajes del buzón.
     * GET /bandeja-sunat/mensajes/{company}?tipo=1&desde=2025-01-01&todo=true
     */
    public function mensajes(Request $request, Company $company): JsonResponse
    {
        abort_if(! $request->user(), 403);

        $token = Cache::get("buzon_token_{$company->id}");
        if (! $token) {
            return response()->json([
                'ok'      => false,
                'error'   => 'Sesión expirada. Haz clic en "Buscar Buzón" nuevamente.',
                'expired' => true,
            ], 401);
        }

        try {
            $response = Http::timeout(120)
                ->get("{$this->botUrl()}/buzon/{$token}", [
                    'tipo'  => $request->query('tipo', 1),
                    'desde' => $request->query('desde', '2025-01-01'),
                    'page'  => $request->query('page', 1),
                    'todo'  => $request->boolean('todo', false) ? 'true' : 'false',
                ]);

            if ($response->status() === 410) {
                Cache::forget("buzon_token_{$company->id}");
                return response()->json([
                    'ok'      => false,
                    'error'   => 'Sesión expirada. Haz clic en "Buscar Buzón" nuevamente.',
                    'expired' => true,
                ], 401);
            }

            return response()->json($response->json());

        } catch (\Exception $e) {
            Log::error('BandejaEntrada::mensajes error: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Detalle de un mensaje.
     * GET /bandeja-sunat/detalle/{company}/{cod}
     */
    public function detalle(Request $request, Company $company, int $cod): JsonResponse
    {
        abort_if(! $request->user(), 403);

        $token = Cache::get("buzon_token_{$company->id}");
        if (! $token) {
            return response()->json(['ok' => false, 'error' => 'Sesión expirada.', 'expired' => true], 401);
        }

        try {
            $response = Http::timeout(30)
                ->get("{$this->botUrl()}/buzon/{$token}/detalle/{$cod}");
            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Descarga el documento HTML de un mensaje.
     * GET /bandeja-sunat/documento/{company}/{cod}
     */
    public function documento(Request $request, Company $company, int $cod): Response
    {
        abort_if(! $request->user(), 403);

        $token = Cache::get("buzon_token_{$company->id}");
        if (! $token) {
            abort(401, 'Sesión expirada.');
        }

        try {
            $response = Http::timeout(30)
                ->get("{$this->botUrl()}/buzon/{$token}/documento/{$cod}");
            return response($response->body(), 200, ['Content-Type' => 'text/html; charset=utf-8']);
        } catch (\Exception $e) {
            abort(500, $e->getMessage());
        }
    }
}
