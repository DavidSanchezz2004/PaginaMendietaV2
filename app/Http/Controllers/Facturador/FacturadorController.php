<?php

namespace App\Http\Controllers\Facturador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\SetActiveCompanyRequest;
use App\Models\Company;
use App\Models\CompanyUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controlador del index del Facturador.
 * Muestra el selector de empresa activa (sin requerir empresa activa previa).
 * Una vez seleccionada, redirige al dashboard del módulo.
 */
class FacturadorController extends Controller
{
    /**
     * Pantalla de entrada al Facturador.
     * Permite seleccionar empresa activa dentro del módulo.
     * No requiere empresa activa en sesión (por diseño).
     */
    public function index(Request $request): View
    {
        $user      = $request->user();
        $activeId  = $request->session()->get('company_id');

        // Obtener empresas accesibles para el facturador según rol
        $companies = $this->getFacturadorCompaniesForUser($user);

        $activeCompany = $activeId
            ? $companies->firstWhere('id', $activeId)
            : null;

        return view('facturador.index', compact('companies', 'activeCompany'));
    }

    /**
     * Establece la empresa activa en sesión para el módulo Facturador.
     * Validado por SetActiveCompanyRequest (verifica membresía + rol).
     */
    public function setActiveCompany(SetActiveCompanyRequest $request): RedirectResponse
    {
        $request->session()->put('company_id', $request->validated('company_id'));

        return redirect()->route('facturador.invoices.index')
            ->with('status', 'Empresa activa actualizada correctamente.');
    }

    /**
     * Obtiene las empresas habilitadas para facturador a las que el usuario tiene acceso.
     */
    private function getFacturadorCompaniesForUser(\App\Models\User $user): \Illuminate\Database\Eloquent\Collection
    {
        $globalRole = $user->role instanceof \App\Enums\RoleEnum
            ? $user->role->value
            : (string) $user->role;

        // Equipo interno global: ve todas las empresas con facturador habilitado
        if (in_array($globalRole, ['admin', 'supervisor'], true)) {
            return Company::where('facturador_enabled', true)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        // Otros roles: solo las empresas donde tiene pivot admin|client activo
        $companyIds = CompanyUser::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['admin', 'client'])
            ->pluck('company_id');

        return Company::whereIn('id', $companyIds)
            ->where('facturador_enabled', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
