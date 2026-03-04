<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturador\UpdateCompanyFacturadorRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

/**
 * Controlador para configurar el módulo Facturador por empresa.
 * Solo el admin interno puede habilitar/deshabilitar y configurar token Feasy.
 *
 * Ruta: PUT /configuracion/companies/{company}/facturador
 */
class CompanyFacturadorController extends Controller
{
    public function update(UpdateCompanyFacturadorRequest $request, Company $company): RedirectResponse
    {
        // La autorización ya se verifica en UpdateCompanyFacturadorRequest::authorize()

        $company->update([
            'facturador_enabled' => $request->validated('facturador_enabled'),
        ]);

        $status = $company->facturador_enabled ? 'habilitado' : 'deshabilitado';

        return redirect()->back()
            ->with('success', "Facturador {$status} para {$company->name}.");
    }
}
