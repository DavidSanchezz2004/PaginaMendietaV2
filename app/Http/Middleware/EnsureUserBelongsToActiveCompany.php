<?php

namespace App\Http\Middleware;

use App\Models\CompanyUser;
use App\Services\Company\ActiveCompanyService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToActiveCompany
{
    public function __construct(
        private readonly ActiveCompanyService $activeCompanyService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'No existe usuario autenticado válido.');
        }

        $activeCompany = $this->activeCompanyService->ensureValidOrInitialize($user, $request->session());

        if (! $activeCompany) {
            // Redirect to dashboard or company selector if logic permits, or abort heavily.
            abort(403, 'No tienes una empresa activa seleccionada o no perteneces a ninguna.');
        }

        // HARDENING AGAINST IDOR: Explicitly query company_user database relation.
        // We do not trust just the session variable or activeCompanyService blind return.
        
        $role = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;
        $isGlobalUser = in_array($role, ['admin', 'supervisor']);

        if (! $isGlobalUser) {
            $hasActiveMembership = CompanyUser::where('user_id', $user->id)
                                            ->where('company_id', $activeCompany->id)
                                            ->where('status', 'active')
                                            ->exists();

            if (! $hasActiveMembership) {
                // Clear session to prevent poisoning
                $request->session()->forget('company_id');
                abort(403, 'Acceso denegado: No cuentas con una vinculación activa (company_user) para procesar esta empresa.');
            }
        }

        return $next($request);
    }
}

