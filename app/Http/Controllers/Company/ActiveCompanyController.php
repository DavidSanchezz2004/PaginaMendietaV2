<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\SwitchActiveCompanyRequest;
use App\Services\Company\ActiveCompanyService;
use Illuminate\Http\RedirectResponse;

class ActiveCompanyController extends Controller
{
    public function update(SwitchActiveCompanyRequest $request, ActiveCompanyService $activeCompanyService): RedirectResponse
    {
        $user = $request->user();

        abort_if(! $user, 403);

        $switched = $activeCompanyService->switchCompany(
            user: $user,
            companyId: (int) $request->integer('company_id'),
            session: $request->session(),
        );

        abort_unless($switched, 403, 'No tienes acceso a la empresa seleccionada.');

        return back()->with('status', 'Empresa activa actualizada correctamente.');
    }
}
