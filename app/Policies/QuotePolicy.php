<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

/**
 * Policy de autorización para Cotizaciones.
 */
class QuotePolicy
{
    /**
     * ¿Puede ver todas las cotizaciones?
     */
    public function viewAny(User $user): bool
    {
        return $this->hasFacturadorRole($user);
    }

    /**
     * ¿Puede ver una cotización específica?
     */
    public function view(User $user, Quote $quote): bool
    {
        return $quote->company_id === session('company_id')
            && $this->hasFacturadorRole($user);
    }

    /**
     * ¿Puede crear cotizaciones?
     */
    public function create(User $user): bool
    {
        return $this->hasFacturadorRole($user);
    }

    /**
     * ¿Puede actualizar una cotización?
     */
    public function update(User $user, Quote $quote): bool
    {
        return $quote->company_id === session('company_id')
            && $this->hasFacturadorRole($user)
            && $quote->estado === 'draft'; // Solo si está en draft
    }

    /**
     * ¿Puede eliminar una cotización?
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $quote->company_id === session('company_id')
            && $this->hasFacturadorRole($user)
            && $quote->estado === 'draft'; // Solo si está en draft
    }

    /**
     * Helper: ¿Tiene rol de facturación?
     */
    private function hasFacturadorRole(User $user): bool
    {
        $companyId = session('company_id');

        if (!$companyId) {
            return false;
        }

        $role = $user->companyUsers()
            ->where('company_id', $companyId)
            ->value('role');

        return in_array($role, ['admin', 'auxiliar', 'supervisor']);
    }
}
