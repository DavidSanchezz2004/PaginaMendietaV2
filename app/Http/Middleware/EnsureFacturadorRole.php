<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use App\Models\CompanyUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garantiza que solo usuarios con rol admin o client en el pivot
 * company_user (para la empresa activa) puedan acceder al Facturador.
 *
 * Orden recomendado en el grupo de rutas:
 *   auth → active.company → facturador.role → facturador.enabled
 *
 * Accesos permitidos:
 *   A) Role global = admin|supervisor  (equipo interno del estudio)
 *   B) Pivot company_user.role in [admin, client] AND status = active
 */
class EnsureFacturadorRole
{
    /** Roles globales que bypasan la restricción de pivot */
    private const GLOBAL_BYPASS = ['admin', 'supervisor'];

    /** Roles de pivot permitidos en el Facturador */
    private const ALLOWED_PIVOT_ROLES = ['admin', 'client'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'No autenticado.');
        }

        $companyId = $request->session()->get('company_id');

        if (! $companyId) {
            // Sin empresa activa → redirigir al index del facturador para seleccionar
            return redirect()->route('facturador.index')
                ->with('warning', 'Selecciona una empresa para continuar.');
        }

        // ── Caso A: admin global bypassa restricción ──────────────────────
        $globalRole = $user->role instanceof RoleEnum
            ? $user->role->value
            : (string) $user->role;

        if (in_array($globalRole, self::GLOBAL_BYPASS, true)) {
            return $next($request);
        }

        // ── Caso B: verificar pivot en BD (no en sesión) ─────────────────
        $pivot = CompanyUser::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->first();

        if (! $pivot) {
            abort(403, 'No tienes membresía activa en esta empresa.');
        }

        $pivotRole = $pivot->role instanceof RoleEnum
            ? $pivot->role->value
            : (string) $pivot->role;

        if (! in_array($pivotRole, self::ALLOWED_PIVOT_ROLES, true)) {
            abort(403, 'Tu rol no tiene acceso al módulo Facturador.');
        }

        return $next($request);
    }
}
