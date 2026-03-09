<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\FinalDocument;
use App\Models\User;

class FinalDocumentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can access the index (filtered by controller)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FinalDocument $finalDocument): bool
    {
        if ($user->role === RoleEnum::ADMIN || $user->role === RoleEnum::SUPERVISOR) {
            return true;
        }

        $activeCompanyId = session('company_id') ?? $user->companies()->first()?->id;
        return $finalDocument->company_id === $activeCompanyId;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === RoleEnum::ADMIN || $user->role === RoleEnum::SUPERVISOR;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FinalDocument $finalDocument): bool
    {
        return $user->role === RoleEnum::ADMIN;
    }
}
