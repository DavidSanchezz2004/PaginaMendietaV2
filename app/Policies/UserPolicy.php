<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $userRole = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, ['admin', 'supervisor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        $userRole = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, ['admin', 'supervisor']);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $userRole = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        $userRole = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        $userRole = $user->role instanceof \App\Enums\RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin';
    }
}
