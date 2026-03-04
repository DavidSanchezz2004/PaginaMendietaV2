<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Services\Company\ActiveCompanyService;
use App\Services\Company\CompanyManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(Request $request, CompanyManagementService $companyManagementService): View
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('manageCompanies', Company::class);

        return view('admin.companies.index', [
            'companies' => $companyManagementService->listUserCompanies($user),
        ]);
    }

    public function create(Request $request): View
    {
        abort_if(! $request->user(), 403);
        $this->authorize('manageCompanies', Company::class);

        return view('admin.companies.create');
    }

    public function edit(Request $request, Company $company): View
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('manageCompanies', Company::class);
        $this->authorize('update', $company);

        return view('admin.companies.create', [
            'company' => $company,
        ]);
    }

    public function store(
        StoreCompanyRequest $request,
        CompanyManagementService $companyManagementService,
    ): RedirectResponse {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('manageCompanies', Company::class);

        $companyManagementService->createCompany($user, $request->validated());

        return redirect()->route('companies.index')->with('status', 'Empresa registrada correctamente.');
    }

    public function update(
        UpdateCompanyRequest $request,
        Company $company,
        CompanyManagementService $companyManagementService,
    ): RedirectResponse {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('manageCompanies', Company::class);
        $this->authorize('update', $company);

        $companyManagementService->updateCompany($company, $request->validated());

        return redirect()->route('companies.index')->with('status', 'Empresa actualizada correctamente.');
    }

    public function destroy(
        Request $request,
        Company $company,
        CompanyManagementService $companyManagementService,
        ActiveCompanyService $activeCompanyService,
    ): RedirectResponse {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('manageCompanies', Company::class);
        $this->authorize('update', $company);

        $companyManagementService->deleteCompany($company);
        $activeCompanyService->ensureValidOrInitialize($user, $request->session());

        return redirect()->route('companies.index')->with('status', 'Empresa eliminada correctamente.');
    }

    public function lookupRuc(Request $request, CompanyManagementService $companyManagementService): JsonResponse
    {
        abort_if(! $request->user(), 403);
        $this->authorize('manageCompanies', Company::class);

        $validated = $request->validate([
            'ruc' => ['required', 'string', 'size:11', 'regex:/^[0-9]{11}$/'],
        ]);

        try {
            $result = $companyManagementService->lookupRuc((string) $validated['ruc']);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'ruc' => $exception->getMessage(),
            ]);
        }
    }
}
