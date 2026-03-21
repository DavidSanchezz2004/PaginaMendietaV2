<?php

namespace App\Http\Controllers\BandejaEntrada;

use App\Http\Controllers\Controller;
use App\Models\BuzonKeyword;
use App\Models\BuzonLectura;
use App\Models\BuzonMensaje;
use App\Models\Company;
use App\Enums\RoleEnum;
use Carbon\Carbon;
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

        $companyIds = $companies->pluck('id')->toArray();
        $counts = BuzonMensaje::whereIn('company_id', $companyIds)
            ->selectRaw('company_id, COUNT(*) as total')
            ->groupBy('company_id')
            ->pluck('total', 'company_id');

        return view('bandeja-sunat.index', [
            'companies' => $companies->values(),
            'filters'   => ['q' => $q],
            'counts'    => $counts,
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

    // ── Nuevos métodos: BD + Keywords ─────────────────────────────────────

    /**
     * Sincroniza mensajes del bot hacia la BD local.
     * POST /bandeja-sunat/sincronizar/{company}
     */
    public function sincronizar(Request $request, Company $company): JsonResponse
    {
        abort_if(! $request->user(), 403);

        $token = Cache::get("buzon_token_{$company->id}");
        if (! $token) {
            return response()->json(['ok' => false, 'error' => 'Sesión expirada. Haz clic en "Buscar Buzón".', 'expired' => true], 401);
        }

        $tipo  = (int) $request->input('tipo', 1);
        $desde = $request->input('desde', now()->subMonths(3)->format('Y-m-d'));

        try {
            $response = Http::timeout(120)->get("{$this->botUrl()}/buzon/{$token}", [
                'tipo'  => $tipo,
                'desde' => $desde,
                'page'  => 1,
                'todo'  => 'true',
            ]);

            $data = $response->json();

            if (! ($data['ok'] ?? false)) {
                return response()->json(['ok' => false, 'error' => $data['error'] ?? 'Error del bot.'], 500);
            }

            $rows    = $data['rows'] ?? [];
            $nuevos  = 0;

            foreach ($rows as $row) {
                $cod = (int) ($row['cod'] ?? 0);
                if ($cod <= 0) {
                    continue;
                }

                $exists = BuzonMensaje::where('company_id', $company->id)
                    ->where('cod_sunat', $cod)
                    ->exists();

                if (! $exists) {
                    BuzonMensaje::create([
                        'company_id' => $company->id,
                        'cod_sunat'  => $cod,
                        'asunto'     => $row['asunto']    ?? null,
                        'remitente'  => $row['remitente'] ?? null,
                        'fecha'      => isset($row['fecha']) ? Carbon::parse($row['fecha'])->toDateString() : null,
                        'tipo'       => $tipo,
                    ]);
                    $nuevos++;
                }
            }

            return response()->json(['ok' => true, 'nuevos' => $nuevos, 'total' => count($rows)]);

        } catch (\Exception $e) {
            Log::error('BandejaEntrada::sincronizar: ' . $e->getMessage());
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Lista mensajes guardados en BD (con prioridad, leido, filtros).
     * GET /bandeja-sunat/lista/{company}?tipo=1&q=&leido=
     */
    public function lista(Request $request, Company $company): JsonResponse
    {
        abort_if(! $request->user(), 403);

        $userId = $request->user()->id;
        $tipo   = (int) $request->query('tipo', 1);
        $q      = (string) $request->query('q', '');
        $leido  = $request->query('leido', ''); // '1', '0', ''

        $query = BuzonMensaje::where('company_id', $company->id)
            ->where('tipo', $tipo)
            ->orderByDesc('fecha')
            ->orderByDesc('id');

        if ($q !== '') {
            $query->where(function ($qb) use ($q): void {
                $qb->where('asunto', 'like', "%{$q}%")
                   ->orWhere('remitente', 'like', "%{$q}%");
            });
        }

        $mensajes = $query->get();

        $keywords = BuzonKeyword::all();

        $result = $mensajes->map(function (BuzonMensaje $m) use ($userId, $keywords): array {
            $prioridad = null;
            $kwColor   = null;
            $asuntoLow = mb_strtolower((string) $m->asunto);

            foreach ($keywords->sortBy(fn ($k) => match ($k->prioridad) {
                'alta' => 0, 'media' => 1, default => 2,
            }) as $kw) {
                if (str_contains($asuntoLow, mb_strtolower($kw->palabra))) {
                    $prioridad = $kw->prioridad;
                    $kwColor   = $kw->color;
                    break;
                }
            }

            $leido = $m->lecturas()->where('user_id', $userId)->exists();

            if ($leido !== null) {
                // ya calculado arriba
            }

            return [
                'id'         => $m->id,
                'cod_sunat'  => $m->cod_sunat,
                'asunto'     => $m->asunto,
                'remitente'  => $m->remitente,
                'fecha'      => $m->fecha?->format('d/m/Y'),
                'prioridad'  => $prioridad,
                'kw_color'   => $kwColor,
                'leido'      => $leido,
            ];
        });

        if ($leido === '1') {
            $result = $result->filter(fn ($r) => $r['leido']);
        } elseif ($leido === '0') {
            $result = $result->filter(fn ($r) => ! $r['leido']);
        }

        return response()->json(['ok' => true, 'rows' => $result->values()]);
    }

    /**
     * Marca un mensaje como leído por el usuario actual.
     * POST /bandeja-sunat/leer/{company}/{mensaje}
     */
    public function marcarLeido(Request $request, Company $company, BuzonMensaje $mensaje): JsonResponse
    {
        abort_if(! $request->user(), 403);
        abort_if($mensaje->company_id !== $company->id, 404);

        $userId = $request->user()->id;

        BuzonLectura::firstOrCreate([
            'buzon_mensaje_id' => $mensaje->id,
            'user_id'          => $userId,
        ], ['leido_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * Vista de gestión de keywords.
     * GET /bandeja-sunat/keywords
     */
    public function keywords(Request $request): JsonResponse
    {
        abort_if(! $request->user(), 403);

        $keywords = BuzonKeyword::orderBy('prioridad')->orderBy('palabra')->get();

        return response()->json(['ok' => true, 'keywords' => $keywords]);
    }

    /**
     * Crea una keyword nueva.
     * POST /bandeja-sunat/keywords
     */
    public function storeKeyword(Request $request): JsonResponse
    {
        abort_if(! $request->user(), 403);

        $validated = $request->validate([
            'palabra'   => ['required', 'string', 'max:100', 'unique:buzon_keywords,palabra'],
            'prioridad' => ['required', 'in:alta,media,baja'],
            'color'     => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $kw = BuzonKeyword::create([
            'palabra'   => mb_strtolower(trim($validated['palabra'])),
            'prioridad' => $validated['prioridad'],
            'color'     => $validated['color'] ?? match ($validated['prioridad']) {
                'alta'  => '#ef4444',
                'media' => '#f59e0b',
                default => '#3b82f6',
            },
        ]);

        return response()->json(['ok' => true, 'keyword' => $kw], 201);
    }

    /**
     * Elimina una keyword.
     * DELETE /bandeja-sunat/keywords/{keyword}
     */
    public function destroyKeyword(Request $request, BuzonKeyword $keyword): JsonResponse
    {
        abort_if(! $request->user(), 403);

        $keyword->delete();

        return response()->json(['ok' => true]);
    }
}
