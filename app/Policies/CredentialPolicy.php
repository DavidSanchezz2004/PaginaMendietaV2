<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Credential;
use App\Models\User;

class CredentialPolicy
{
    private array $globalRoles = ['admin', 'supervisor'];

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Credential $credential): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        if (in_array($userRole, $this->globalRoles, true)) {
            return true;
        }

        // Anti-IDOR
        return $credential->company_id === session('company_id');
    }

    public function create(User $user): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, ['admin', 'supervisor', 'auxiliar'], true);
    }

    public function update(User $user, Credential $credential): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        if ($userRole === 'admin') {
            return true;
        }

        if ($userRole === 'auxiliar') {
            return $credential->company_id === session('company_id');
        }

        return false;
    }

    public function delete(User $user, Credential $credential): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin';
    }
}

