<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueo triple del Facturador cuando la empresa activa
 * no tiene facturador_enabled = true.
 *
 * Orden en el grupo:
 *   auth → active.company → facturador.role → facturador.enabled
 *
 * Si el módulo no está habilitado:
 *   - Peticiones JSON/API → 403 JSON
 *   - Peticiones web → 403 con vista de error explicativa
 */
class EnsureFacturadorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        // Admin global bypasea la restricción de facturador_enabled
        $user = $request->user();
        if ($user) {
            $globalRole = $user->role instanceof RoleEnum
                ? $user->role->value
                : (string) $user->role;
            if ($globalRole === 'admin') {
                return $next($request);
            }
        }

        $companyId = $request->session()->get('company_id');

        if (! $companyId) {
            return redirect()->route('facturador.index')
                ->with('warning', 'Selecciona una empresa para continuar.');
        }

        /** @var Company|null $company */
        $company = Company::select(['id', 'facturador_enabled', 'name'])
            ->find($companyId);

        if (! $company) {
            // La empresa ya no existe o fue desactivada → limpiar sesión
            $request->session()->forget('company_id');
            abort(403, 'La empresa activa no existe.');
        }

        if (! $company->facturador_enabled) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El módulo Facturador no está habilitado para esta empresa.',
                ], Response::HTTP_FORBIDDEN);
            }

            abort(403, 'El módulo Facturador no está habilitado para la empresa "' . $company->name . '". Contacta al administrador.');
        }

        return $next($request);
    }
}
