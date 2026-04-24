<?php

namespace App\Policies;

use App\Models\GuiaRemision;
use App\Models\User;
use App\Policies\Concerns\FacturadorPolicyTrait;

class GuiaRemisionPolicy
{
    use FacturadorPolicyTrait;

    public function viewAny(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function view(User $user, GuiaRemision $guia): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($guia);
    }

    public function create(User $user): bool
    {
        // Permitir a cualquier usuario autenticado del facturador por ahora
        // La autorización se hace a nivel de middleware (active.company, facturador.role)
        return true;
    }

    public function update(User $user, GuiaRemision $guia): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($guia);
    }

    public function delete(User $user, GuiaRemision $guia): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($guia);
    }
}
