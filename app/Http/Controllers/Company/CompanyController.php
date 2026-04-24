<?php

namespace App\Http\Controllers\Company;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
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
        // viewAny es true para todos; el listado filtra por rol internamente
        $this->authorize('viewAny', Company::class);

        return view('admin.companies.index', [
            'companies' => $companyManagementService->listUserCompanies($user),
        ]);
    }

    public function create(Request $request): View
    {
        abort_if(! $request->user(), 403);
        $this->authorize('create', Company::class);

        return view('admin.companies.create');
    }

    public function edit(Request $request, Company $company): View
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('manageCompanies', Company::class);
        $this->authorize('update', $company);

        // Usuarios asignados con datos del pivot
        $assignedUsers = $company->users()->get();

        // Usuarios del sistema que pueden ser asignados (excluir ya asignados)
        $assignedIds     = $assignedUsers->pluck('id');
        $assignableUsers = User::whereNotIn('id', $assignedIds)->orderBy('name')->get();

        return view('admin.companies.create', [
            'company'         => $company,
            'assignedUsers'   => $assignedUsers,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    public function store(
        StoreCompanyRequest $request,
        CompanyManagementService $companyManagementService,
    ): RedirectResponse {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('create', Company::class);

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
        $this->authorize('create', Company::class);

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

    /**
     * Lista usuarios asignados a una empresa (misma vista que edit).
     */
    public function companyUsers(Request $request, Company $company): View
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('update', $company);

        $assignedUsers   = $company->users()->get();
        $assignedIds     = $assignedUsers->pluck('id');
        $assignableUsers = User::whereNotIn('id', $assignedIds)->orderBy('name')->get();

        return view('admin.companies.create', [
            'company'         => $company,
            'assignedUsers'   => $assignedUsers,
            'assignableUsers' => $assignableUsers,
        ]);
    }

    /**
     * Asigna un usuario existente a la empresa como auxiliar.
     */
    public function assignUser(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('update', $company);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role'    => ['required', 'string', 'in:auxiliar,supervisor,client,admin'],
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        $company->users()->syncWithoutDetaching([
            $targetUser->id => [
                'role'   => $validated['role'],
                'status' => 'active',
            ],
        ]);

        return redirect()
            ->route('companies.edit', $company)
            ->with('status', "Usuario «{$targetUser->name}» asignado a la empresa correctamente.");
    }

    /**
     * Quita a un usuario de la empresa (no puede quitar al admin ni supervisor dueño).
     */
    public function removeUser(Request $request, Company $company, User $companyUser): RedirectResponse
    {
        $user = $request->user();
        abort_if(! $user, 403);
        $this->authorize('update', $company);

        $userRole = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;

        // Solo admin puede quitar a cualquiera; supervisor solo puede quitar auxiliares
        $pivot = $company->users()->where('users.id', $companyUser->id)->first()?->pivot;

        if (! $pivot) {
            return redirect()->route('companies.edit', $company)->with('error', 'El usuario no pertenece a esta empresa.');
        }

        $pivotRole = $pivot->role instanceof RoleEnum ? $pivot->role->value : (string) $pivot->role;

        if ($userRole !== 'admin' && $pivotRole !== 'auxiliar') {
            return redirect()->route('companies.edit', $company)
                ->with('error', 'Solo puedes quitar usuarios con rol auxiliar.');
        }

        $company->users()->detach($companyUser->id);

        return redirect()
            ->route('companies.edit', $company)
            ->with('status', "Usuario «{$companyUser->name}» removido de la empresa.");
    }
}
