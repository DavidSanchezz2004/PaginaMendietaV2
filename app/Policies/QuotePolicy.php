<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use App\Policies\Concerns\FacturadorPolicyTrait;

/**
 * Policy de autorización para Cotizaciones.
 */
class QuotePolicy
{
    use FacturadorPolicyTrait;

    /**
     * ¿Puede ver todas las cotizaciones?
     */
    public function viewAny(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    /**
     * ¿Puede ver una cotización específica?
     */
    public function view(User $user, Quote $quote): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($quote);
    }

    /**
     * ¿Puede crear cotizaciones?
     */
    public function create(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    /**
     * ¿Puede actualizar una cotización?
     */
    public function update(User $user, Quote $quote): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($quote)
            && $quote->estado === 'draft'; // Solo si está en draft
    }

    /**
     * ¿Puede eliminar una cotización?
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($quote)
            && $quote->estado === 'draft'; // Solo si está en draft
    }
}
