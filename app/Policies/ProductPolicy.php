<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\FacturadorPolicyTrait;

/**
 * Policy para el catálogo de Productos del Facturador.
 * Regla transversal: todos los métodos validan canAccessFacturador()
 * y resourceBelongsToActiveCompany() (Anti-IDOR).
 */
class ProductPolicy
{
    use FacturadorPolicyTrait;

    public function viewAny(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function view(User $user, Product $product): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($product);
    }

    public function create(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function update(User $user, Product $product): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($product);
    }

    public function delete(User $user, Product $product): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($product);
    }
}
