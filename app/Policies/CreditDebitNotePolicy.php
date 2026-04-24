<?php

namespace App\Policies;

use App\Models\CreditDebitNote;
use App\Models\User;
use App\Policies\Concerns\FacturadorPolicyTrait;

class CreditDebitNotePolicy
{
    use FacturadorPolicyTrait;

    public function viewAny(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function view(User $user, CreditDebitNote $note): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($note);
    }

    public function create(User $user): bool
    {
        return $this->canAccessFacturador($user);
    }

    public function update(User $user, CreditDebitNote $note): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($note);
    }

    public function delete(User $user, CreditDebitNote $note): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($note);
    }

    public function restore(User $user, CreditDebitNote $note): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($note);
    }

    public function forceDelete(User $user, CreditDebitNote $note): bool
    {
        return $this->canAccessFacturador($user)
            && $this->resourceBelongsToActiveCompany($note);
    }
}
