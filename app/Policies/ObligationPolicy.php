<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Obligation;
use App\Models\User;

class ObligationPolicy
{
    private array $globalRoles = ['admin', 'supervisor'];

    public function viewAny(User $user): bool
    {
        return true; 
    }

    public function view(User $user, Obligation $obligation): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        if (in_array($userRole, $this->globalRoles, true)) {
            return true;
        }

        // Anti-IDOR
        return $obligation->company_id === session('company_id');
    }

    public function create(User $user): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        // Auxiliar también puede crear obligaciones para las empresas asignadas 
        return in_array($userRole, ['admin', 'supervisor', 'auxiliar'], true);
    }

    public function update(User $user, Obligation $obligation): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        if ($userRole === 'admin') {
            return true;
        }

        // Auxiliar edita si la empresa es la activa y coincide
        if ($userRole === 'auxiliar') {
            return $obligation->company_id === session('company_id');
        }

        return false;
    }

    public function delete(User $user, Obligation $obligation): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin';
    }
}

