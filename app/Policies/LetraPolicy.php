<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\LetraCambio;
use App\Models\User;

class LetraPolicy
{
    /**
     * Ver todas las letras de la empresa activa.
     */
    public function viewAny(User $user): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, ['admin', 'auxiliar', 'supervisor', 'client'], true);
    }

    /**
     * Ver una letra específica (solo si es de la empresa activa).
     */
    public function view(User $user, LetraCambio $letra): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        return (int) $letra->company_id === (int) session('company_id')
            && in_array($userRole, ['admin', 'auxiliar', 'supervisor', 'client'], true);
    }

    /**
     * Marcar letra como pagada (solo admins/auxiliar/supervisor).
     */
    public function update(User $user, LetraCambio $letra): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        return (int) $letra->company_id === (int) session('company_id')
            && in_array($userRole, ['admin', 'auxiliar', 'supervisor'], true);
    }

    /**
     * Eliminar letra (solo si está pendiente y sin pagos).
     */
    public function delete(User $user, LetraCambio $letra): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        return (int) $letra->company_id === (int) session('company_id')
            && in_array($userRole, ['admin', 'auxiliar', 'supervisor'], true)
            && $letra->estado === 'pendiente'
            && $letra->monto_pagado === 0;
    }
}
