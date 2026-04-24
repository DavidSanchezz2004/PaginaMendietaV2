<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Policies\Concerns\FacturadorPolicyTrait;

/**
 * Policy para el catálogo de Clientes/Receptores del Facturador.
 * Regla transversal: canAccessFacturador() + Anti-IDOR.
 */
class ClientPolicy
{
    use FacturadorPolicyTrait;

    public function viewAny(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function view(User $user, Client $client): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($client);
    }

    public function create(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function update(User $user, Client $client): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($client);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($client);
    }
}
