<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use App\Policies\Concerns\FacturadorPolicyTrait;

class PurchasePolicy
{
    use FacturadorPolicyTrait;

    public function viewAny(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($purchase);
    }

    public function create(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($purchase);
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($purchase);
    }
}
