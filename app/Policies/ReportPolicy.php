<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Enums\RoleEnum;

class ReportPolicy
{
    private array $globalRoles = ['admin', 'supervisor'];

    public function viewAny(User $user): bool
    {
        // Todos pueden ver el listado (el controlador se encarga de filtrar por $activeCompanyId)
        return true; 
    }

    public function view(User $user, Report $report): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        // Anti-IDOR: Si no es admin/supervisor, el reporte debe pertenecer a su empresa activa en sesión
        if (!in_array($userRole, $this->globalRoles, true)) {
            if ($report->company_id !== session('company_id')) {
                return false;
            }
        }

        if (in_array($userRole, ['admin', 'supervisor', 'auxiliar'], true)) {
            return true; // Ven publicados y borradores
        }

        // Cliente solo puede ver publicados
        if (in_array($userRole, ['client', 'cliente'], true)) {
            return $report->status === 'published';
        }

        return false;
    }

    public function create(User $user): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, ['admin', 'supervisor', 'auxiliar'], true);
    }

    public function update(User $user, Report $report): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        // Anti-IDOR
        if (!in_array($userRole, $this->globalRoles, true) && $report->company_id !== session('company_id')) {
            return false;
        }

        if ($userRole === 'admin') {
            return true;
        }

        if ($userRole === 'auxiliar') {
            // Auxiliar solo puede editar si está en draft (regla de negocio validada en Matriz)
            return $report->status === 'draft';
        }

        return false;
    }

    public function delete(User $user, Report $report): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin';
    }

    public function publish(User $user, Report $report): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, $this->globalRoles, true);
    }
    
    public function unpublish(User $user, Report $report): bool
    {
        return $this->publish($user, $report);
    }

    public function download(User $user, Report $report): bool
    {
        return $this->view($user, $report); // Misma regla de visualización
    }
}

