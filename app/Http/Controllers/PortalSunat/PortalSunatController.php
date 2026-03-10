<?php

namespace App\Http\Controllers\PortalSunat;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ObligationDeclaration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PortalSunatController extends Controller
{
    /**
     * Lista todas las empresas disponibles para el usuario según su rol.
     * GET /portal-sunat
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        // ADMIN ve todas; el resto solo las que tienen asignadas
        if ($role === 'admin') {
            $companies = Company::orderBy('name')->get();
        } else {
            $companies = $user->companies()
                ->wherePivot('status', 'active')
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
            'companies'       => $companies->values(),
            'filters'         => ['q' => $q, 'last_digit' => $lastDigit],
            'userRole'        => $role,
            'cronogramaStats' => $cronogramaStats,
        ]);
    }

    /**
     * Muestra el formulario para editar credenciales SOL de una empresa.
     * GET /portal-sunat/{company}/credenciales
     */
    public function credentials(Request $request, Company $company): View
    {
        $this->authorize('updateSunatCredentials', $company);

        return view('portal-sunat.credentials', [
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
            'usuario_sol' => ['required', 'string', 'max:50'],
            'clave_sol'   => ['nullable', 'string', 'max:255'],
        ]);

        $update = ['usuario_sol' => strtoupper(trim($validated['usuario_sol']))];

        if (! empty($validated['clave_sol'])) {
            $update['clave_sol'] = $validated['clave_sol'];
        }

        $company->update($update);

        // Si viene desde la edición de empresa, redirigir de vuelta allí
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
     * Llama al bot SUNAT y devuelve la URL de inyección para la extensión Chrome.
     * GET /portal-sunat/{company}/abrir
     */
    public function open(Request $request, Company $company): JsonResponse
    {
        abort_if(! $request->user(), 403);

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

            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'  => $botKey,
                    'User-Agent' => 'LaravelBot/1.0',
                    'Accept'     => 'application/json',
                ])
                ->post("{$botUrl}/proxy/create", [
                    'ruc'         => $company->ruc,
                    'usuario_sol' => $company->usuario_sol,
                    'clave_sol'   => $company->clave_sol,
                    'portal'      => 'sunat',
                ]);

            $data = $response->json();

            if (! ($data['ok'] ?? false)) {
                return response()->json([
                    'ok'    => false,
                    'error' => $data['detalle'] ?? $data['error'] ?? 'Error del bot.',
                ], 500);
            }

            return response()->json([
                'ok'  => true,
                'url' => "{$botUrl}/ext-inject/{$data['token']}",
            ]);

        } catch (\Exception $e) {
            Log::error('PortalSunat::open error: ' . $e->getMessage());

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
