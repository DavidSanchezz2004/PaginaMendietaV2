<?php

namespace App\Policies;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    /**
     * @var array<string> Roles permitidos para ver el panorama general.
     */
    private array $globalRoles = ['admin', 'supervisor'];

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Company $company): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        if (in_array($userRole, $this->globalRoles, true)) {
            return true;
        }

        // Anti-IDOR: Si no es admin/supervisor, SOLAMENTE puede ver su empresa activa en sesion
        if ($company->id !== session('company_id')) {
            return false;
        }

        // Verificar membresía activa
        return $user->companyUsers()
            ->where('company_id', $company->id)
            ->where('status', 'active')
            ->exists();
    }

    public function create(User $user): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        // Auxiliar NO puede crear empresas
        return $userRole !== 'auxiliar';
    }

    public function update(User $user, Company $company): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, $this->globalRoles, true);
    }

    public function delete(User $user, Company $company): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        // Solo el ADMIN puede borrar empresas (según la matriz)
        return $userRole === 'admin';
    }

    public function assignUsers(User $user, Company $company): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin'; // Restringido solo a admin
    }

    /**
     * Alias general para gestionar el panorama de empresas.
     * Solo Admin y Supervisor pueden gestionar (crear/editar).
     * Auxiliar, Accountant y Client solo pueden ver, no gestionar.
     */
    public function manageCompanies(User $user): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($userRole, ['admin', 'supervisor']);
    }

    /**
     * Acción para que auxiliares o admin gestionen usuarios a nivel empresa
     */
    public function manageUsers(User $user, Company $company): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        // Solo el Admin o Auxiliar pueden gestionar usuarios de una empresa dada la lógica actual, 
        // pero alineado a la matriz, dejaremos que sea Admin por defecto.
        return $userRole === 'admin';
    }

    /**
     * Habilitar/deshabilitar el módulo Facturador para una empresa
     * y gestionar el token Feasy de esa empresa.
     *
     * SOLO el admin interno (role global = admin) puede hacer esto.
     * El rol cliente NUNCA puede tocar la configuración del facturador.
     */
    public function updateFacturadorConfig(User $user, ?Company $company = null): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return $userRole === 'admin';
    }

    /**
     * Editar credenciales SOL de una empresa.
     *
     * ADMIN      → puede editar cualquier empresa
     * SUPERVISOR → puede editar empresas a las que pertenece
     * CLIENTE    → puede editar sus propias empresas
     * AUXILIAR   → NO puede editar credenciales
     */
    public function updateSunatCredentials(User $user, Company $company): bool
    {
        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        if ($userRole === 'auxiliar') {
            return false;
        }

        if ($userRole === 'admin') {
            return true;
        }

        // supervisor, client: solo si tiene membresía activa en esa empresa
        return $user->companies()
            ->where('companies.id', $company->id)
            ->wherePivot('status', 'active')
            ->exists();
    }
}

