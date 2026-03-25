<?php

namespace App\Http\Controllers\PortalSunat;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ObligationDeclaration;
use App\Models\CompanyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PortalSunatController extends Controller
{
    /** Portales válidos expuestos al frontend */
    private const PORTALES = [
        'sunat'       => 'Menú SOL',
        'declaracion' => 'Declaración y Pago',
        'sunafil'     => 'Casilla SUNAFIL',
    ];

    /**
     * Lista todas las empresas disponibles para el usuario según su rol.
     * GET /portal-sunat
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        $view = (string) $request->query('view', 'active'); // active | archived | all

        // Lista de empresas ocultas para este usuario (para filtros y vista)
        $hiddenCompanyIds = CompanyUser::query()
            ->where('user_id', $user->id)
            ->where('hidden_in_dashboard', true)
            ->pluck('company_id');

        // ADMIN y SUPERVISOR: ven todas las empresas, con opción de ver solo archivadas.
        if (in_array($role, ['admin', 'supervisor'], true)) {
            $companiesQuery = Company::query();

            if ($view === 'archived') {
                $companiesQuery->whereIn('id', $hiddenCompanyIds);
            } elseif ($view === 'active') {
                if ($hiddenCompanyIds->isNotEmpty()) {
                    $companiesQuery->whereNotIn('id', $hiddenCompanyIds);
                }
            }

            $companies = $companiesQuery
                ->orderBy('name')
                ->get();
        } else {
            // Otros roles: solo empresas asignadas y activas, respetando ocultas.
            $companiesQuery = $user->companies()
                ->wherePivot('status', 'active');

            if ($view === 'archived') {
                $companiesQuery->where('company_user.hidden_in_dashboard', true);
            } elseif ($view === 'active') {
                $companiesQuery->where(function ($q) {
                    $q->where('company_user.hidden_in_dashboard', false)
                      ->orWhereNull('company_user.hidden_in_dashboard');
                });
            }

            $companies = $companiesQuery
                ->orderBy('name')
                ->get();
        }

        // Filtros opcionales
        $q         = (string) $request->query('q', '');
        $lastDigit = (string) $request->query('last_digit', '');

        if ($q !== '') {
            $companies = $companies->filter(
                fn ($c) => str_contains(mb_strtolower($c->name), mb_strtolower($q))
            );
        }

        if ($lastDigit !== '') {
            $companies = $companies->filter(
                fn ($c) => substr((string) $c->ruc, -1) === $lastDigit
            );
        }

        // ── Resumen cronograma del período anterior (el que se está declarando ahora)
        $cronogramaStats = null;
        if ($role !== 'client') {
            $cronoYear  = now()->month === 1 ? now()->year - 1 : now()->year;
            $cronoMonth = now()->month === 1 ? 12 : now()->month - 1;
            $allIds     = $companies->pluck('id');
            $declared   = ObligationDeclaration::whereIn('company_id', $allIds)
                ->where('period_year',  $cronoYear)
                ->where('period_month', $cronoMonth)
                ->count();
            $cronoTotal = $companies->count();
            $cronogramaStats = [
                'year'     => $cronoYear,
                'month'    => $cronoMonth,
                'declared' => $declared,
                'pending'  => max(0, $cronoTotal - $declared),
                'total'    => $cronoTotal,
                'pct'      => $cronoTotal > 0 ? round($declared / $cronoTotal * 100) : 0,
            ];
        }

        return view('portal-sunat.index', [
            'companies'        => $companies->values(),
            'filters'          => ['q' => $q, 'last_digit' => $lastDigit, 'view' => $view],
            'userRole'         => $role,
            'cronogramaStats'  => $cronogramaStats,
            'hiddenCompanyIds' => $hiddenCompanyIds,
        ]);
    }

    public function hideForUser(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        abort_if(! in_array($role, ['admin', 'supervisor'], true), 403);

        $now = now();

        DB::table('company_user')->updateOrInsert(
            ['user_id' => $user->id, 'company_id' => $company->id],
            [
                'role'                => $role,
                'status'              => 'active',
                'hidden_in_dashboard' => true,
                'updated_at'          => $now,
                'created_at'          => $now,
            ]
        );

        return back()->with('success', "Empresa «{$company->name}» archivada para tu vista.");
    }

    public function unhideForUser(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        abort_if(! in_array($role, ['admin', 'supervisor'], true), 403);

        $now = now();

        DB::table('company_user')->updateOrInsert(
            ['user_id' => $user->id, 'company_id' => $company->id],
            [
                'role'                => $role,
                'status'              => 'active',
                'hidden_in_dashboard' => false,
                'updated_at'          => $now,
                'created_at'          => $now,
            ]
        );

        return back()->with('success', "Empresa «{$company->name}» restaurada en tu vista.");
    }

    /**
     * Muestra el formulario para editar credenciales SOL de una empresa.
     * GET /portal-sunat/{company}/credenciales
     */
    public function credentials(Request $request, Company $company): View
    {
        $this->authorize('updateSunatCredentials', $company);

        return view('portal-sunat.credentials-form', [
            'company' => $company,
        ]);
    }

    /**
     * Guarda las credenciales SOL de una empresa.
     * PUT /portal-sunat/{company}/credenciales
     */
    public function updateCredentials(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('updateSunatCredentials', $company);

        $validated = $request->validate([
            'usuario_sol'    => ['required', 'string', 'max:50'],
            'clave_sol'      => ['nullable', 'string', 'max:255'],
            'afpnet_usuario' => ['nullable', 'string', 'max:100'],
            'afpnet_clave'   => ['nullable', 'string', 'max:255'],
        ]);

        $update = ['usuario_sol' => strtoupper(trim($validated['usuario_sol']))];

        if (! empty($validated['clave_sol'])) {
            $update['clave_sol'] = $validated['clave_sol'];
        }

        if (array_key_exists('afpnet_usuario', $validated)) {
            $update['afpnet_usuario'] = trim($validated['afpnet_usuario'] ?? '') ?: null;
        }

        if (! empty($validated['afpnet_clave'])) {
            $update['afpnet_clave'] = $validated['afpnet_clave'];
        }

        $company->update($update);

        $redirectTo = $request->input('redirect_back');
        if ($redirectTo === 'companies.edit') {
            return redirect()
                ->route('companies.edit', $company)
                ->with('status', "Credenciales SOL de «{$company->name}» actualizadas correctamente.");
        }

        return redirect()
            ->route('portal-sunat.index')
            ->with('success', "Credenciales de «{$company->name}» actualizadas correctamente.");
    }

    /**
     * Abre el portal SUNAT o SUNAFIL para una empresa vía el bot de cookies.
     *
     * El bot hace login con Playwright, captura las cookies de sesión y las
     * expone a través de un token. La extensión Chrome hace polling al bot,
     * recoge las cookies y las inyecta en el navegador del usuario para que
     * SUNAT/SUNAFIL lo reconozca como autenticado.
     *
     * GET /portal-sunat/{company}/abrir?portal=sunat|declaracion|sunafil
     *     (portal por defecto: 'sunat')
     */
    public function open(Request $request, Company $company): JsonResponse
    {
        abort_if(! $request->user(), 403);

        // Validar portal recibido (default: sunat para backwards-compat)
        $portal = $request->query('portal', 'sunat');
        if (! array_key_exists($portal, self::PORTALES)) {
            return response()->json([
                'ok'    => false,
                'error' => "Portal inválido. Valores permitidos: " . implode(', ', array_keys(self::PORTALES)),
            ], 422);
        }

        if (! $company->canUseSunatPortal()) {
            return response()->json([
                'ok'    => false,
                'error' => 'La empresa está inactiva.',
            ], 422);
        }

        if (! $company->hasSunatCredentials()) {
            return response()->json([
                'ok'    => false,
                'error' => 'La empresa no tiene credenciales SOL configuradas.',
            ], 422);
        }

        try {
            $botUrl = rtrim((string) config('services.sunat_bot.url'), '/');
            $botKey = (string) config('services.sunat_bot.key');

            if (! $botUrl || ! $botKey) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'SUNAT bot no configurado (SUNAT_BOT_URL / SUNAT_API_KEY).',
                ], 500);
            }

            // Cada portal tiene su alias dedicado en el bot
            $endpoint = match ($portal) {
                'declaracion' => "{$botUrl}/declaracion/proxy/create",
                'sunafil'     => "{$botUrl}/sunafil/proxy/create",
                default       => "{$botUrl}/proxy/create",  // sunat
            };

            $http = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'  => $botKey,
                    'User-Agent' => 'LaravelBot/1.0',
                    'Accept'     => 'application/json',
                ]);

            // En local/ngrok el CA bundle de cURL no puede verificar el certificado
            if (app()->isLocal() || str_contains($botUrl, 'ngrok')) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($endpoint, [
                    'ruc'         => $company->ruc,
                    'usuario_sol' => $company->usuario_sol,
                    'clave_sol'   => $company->clave_sol,
                    'portal'      => $portal,
                ]);

            $data = $response->json();

            if (! ($data['ok'] ?? false)) {
                Log::warning("PortalSunat::open [{$portal}] bot error", [
                    'company' => $company->id,
                    'ruc'     => $company->ruc,
                    'error'   => $data['error']   ?? null,
                    'detalle' => $data['detalle']  ?? null,
                ]);

                return response()->json([
                    'ok'    => false,
                    'error' => $data['detalle'] ?? $data['error'] ?? 'Error del bot.',
                ], 500);
            }

            Log::info("PortalSunat::open [{$portal}] login iniciado", [
                'company' => $company->id,
                'ruc'     => $company->ruc,
                'token'   => substr($data['token'] ?? '', 0, 8) . '...',
            ]);

            return response()->json([
                'ok'     => true,
                'portal' => $portal,
                'url'    => "{$botUrl}/ext-inject/{$data['token']}",
            ]);

        } catch (\Exception $e) {
            Log::error("PortalSunat::open [{$portal}] exception: " . $e->getMessage(), [
                'company' => $company->id,
            ]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}