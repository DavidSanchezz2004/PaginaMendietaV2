<?php

namespace App\Policies\Concerns;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;

/**
 * Trait compartido por todas las Policies del módulo Facturador.
 *
 * Centraliza la verificación de:
 *  1) Usuario pertenece a la empresa activa con pivot activo.
 *  2) Rol pivot es admin o client (únicos roles del facturador).
 *  3) El recurso pertenece a la empresa activa (Anti-IDOR).
 *
 * REGLA: ninguna operación del facturador se autoriza sin pasar
 * los tres controles anteriores.
 */
trait FacturadorPolicyTrait
{
    /**
     * Roles globales del sistema que tienen acceso total al facturador.
     * El admin global puede operar sobre cualquier empresa.
     */
    private array $globalRoles = ['admin'];

    /**
     * Roles por empresa (company_user.role) que pueden usar el facturador.
     */
    private array $facturadorPivotRoles = ['admin', 'client'];

    /**
     * ¿El usuario tiene acceso al facturador para la empresa activa?
     *
     * Casos permitidos:
     *  A) Es admin global (user.role = admin).
     *  B) Tiene pivot activo en company_user con role admin|client.
     */
    protected function canAccessFacturador(User $user): bool
    {
        // Caso A: admin global bypassa restricción de pivot role y empresa activa
        $globalRole = $user->role instanceof RoleEnum
            ? $user->role->value
            : (string) $user->role;

        if (in_array($globalRole, $this->globalRoles, true)) {
            return true;
        }

        $companyId = session('company_id');

        if (! $companyId) {
            return false;
        }

        // La empresa activa debe tener el facturador habilitado
        $company = Company::find($companyId);
        if (! $company || ! $company->facturador_enabled) {
            return false;
        }

        // Caso B: verificar pivot estrictamente en BD (no en sesión)
        $pivot = CompanyUser::where('user_id', $user->id)
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->first();

        if (! $pivot) {
            return false;
        }

        $pivotRole = $pivot->role instanceof RoleEnum
            ? $pivot->role->value
            : (string) $pivot->role;

        return in_array($pivotRole, $this->facturadorPivotRoles, true);
    }

    /**
     * Anti-IDOR: verifica que el recurso (con company_id) pertenezca
     * a la empresa activa en sesión.
     *
     * @param object $resource  Cualquier modelo con propiedad company_id.
     */
    protected function resourceBelongsToActiveCompany(object $resource): bool
    {
        return (int) $resource->company_id === (int) session('company_id');
    }
}
