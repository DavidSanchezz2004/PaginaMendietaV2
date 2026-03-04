<?php

namespace App\Http\Controllers\User;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateManagedUserRequest;
use App\Http\Requests\User\StoreCompanyUserRequest;
use App\Http\Requests\User\UpdateUserAssignmentsRequest;
use App\Models\Company;
use App\Models\User;
use App\Services\Company\ActiveCompanyService;
use App\Services\User\CompanyUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyUserController extends Controller
{
    public function index(
        Request $request,
        ActiveCompanyService $activeCompanyService,
        CompanyUserService $companyUserService,
    ): View {
        $user = $request->user();
        abort_if(! $user, 403);

        $isGlobalAdmin = $user->can('manageCompanies', Company::class);

        if ($isGlobalAdmin) {
            return view('admin.users.index', [
                'isGlobalAdmin' => true,
                'activeCompany' => null,
                'companyUsers' => collect(),
                'memberships' => $companyUserService->listActiveMemberships(),
                'globalUsers' => $companyUserService->listGlobalUsers(),
            ]);
        }

        $activeCompany = $activeCompanyService->ensureValidOrInitialize($user, $request->session());
        abort_if(! $activeCompany, 403);

        $this->authorize('manageUsers', $activeCompany);

        $companyUsers = $companyUserService->listCompanyUsers($activeCompany);

        return view('admin.users.index', [
            'isGlobalAdmin' => false,
            'activeCompany' => $activeCompany,
            'companyUsers' => $companyUsers,
            'memberships' => collect(),
            'globalUsers' => collect(),
        ]);
    }

    public function create(
        Request $request,
        ActiveCompanyService $activeCompanyService,
        CompanyUserService $companyUserService,
    ): View
    {
        $user = $request->user();
        abort_if(! $user, 403);

        $isGlobalAdmin = $user->can('manageCompanies', Company::class);

        if ($isGlobalAdmin) {
            return view('admin.users.create', [
                'activeCompany' => null,
                'isGlobalAdmin' => true,
                'companies' => $companyUserService->listAssignableCompanies(),
                'roles' => RoleEnum::cases(),
            ]);
        }

        $activeCompany = $activeCompanyService->ensureValidOrInitialize($user, $request->session());
        abort_if(! $activeCompany, 403);

        $this->authorize('manageUsers', $activeCompany);

        return view('admin.users.create', [
            'activeCompany' => $activeCompany,
            'isGlobalAdmin' => false,
            'companies' => collect(),
            'roles' => RoleEnum::cases(),
        ]);
    }

    public function store(
        StoreCompanyUserRequest $request,
        ActiveCompanyService $activeCompanyService,
        CompanyUserService $companyUserService,
    ): RedirectResponse {
        $user = $request->user();
        abort_if(! $user, 403);

        $isGlobalAdmin = $user->can('manageCompanies', Company::class);

        if ($isGlobalAdmin) {
            $validated = $request->validated();
            $createdUser = $companyUserService->createGlobalUser($validated);

            return redirect()
                ->route('users.assignments.edit', $createdUser)
                ->with('status', 'Usuario global creado correctamente. Ahora puedes asignarle empresas si corresponde.');
        }

        $activeCompany = $activeCompanyService->ensureValidOrInitialize($user, $request->session());
        abort_if(! $activeCompany, 403);

        $this->authorize('manageUsers', $activeCompany);

        $companyUserService->createCompanyUser($activeCompany, $request->validated());

        return redirect()->route('users.index')->with('status', 'Usuario registrado/asignado correctamente.');
    }

    public function editAssignments(Request $request, User $managedUser, CompanyUserService $companyUserService): View
    {
        $user = $request->user();
        abort_if(! $user, 403);
        abort_if(! $user->can('manageCompanies', Company::class), 403);

        $managedUser = $companyUserService->findUserWithCompanies($managedUser->id);
        abort_if(! $managedUser, 404);

        $companies = $companyUserService->listAssignableCompanies();

        $currentAssignments = $managedUser->companies
            ->mapWithKeys(function ($company): array {
                $role = $company->pivot?->role;

                return [
                    $company->id => [
                        'role' => $role instanceof RoleEnum ? $role->value : (string) $role,
                        'status' => (string) ($company->pivot?->status ?? 'active'),
                    ],
                ];
            })
            ->all();

        return view('admin.users.assignments', [
            'managedUser' => $managedUser,
            'companies' => $companies,
            'roles' => RoleEnum::cases(),
            'currentAssignments' => $currentAssignments,
        ]);
    }

    public function edit(Request $request, User $managedUser, CompanyUserService $companyUserService): View
    {
        $user = $request->user();
        abort_if(! $user, 403);
        abort_if(! $user->can('manageCompanies', Company::class), 403);

        $managedUser = $companyUserService->findUserWithCompanies($managedUser->id) ?? $managedUser;

        return view('admin.users.edit', [
            'managedUser' => $managedUser,
            'roles' => RoleEnum::cases(),
        ]);
    }

    public function update(
        UpdateManagedUserRequest $request,
        User $managedUser,
        CompanyUserService $companyUserService,
    ): RedirectResponse {
        $user = $request->user();
        abort_if(! $user, 403);
        abort_if(! $user->can('manageCompanies', Company::class), 403);

        $companyUserService->updateManagedUser($managedUser, $request->validated());

        return redirect()->route('users.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function updateAssignments(
        UpdateUserAssignmentsRequest $request,
        User $managedUser,
        CompanyUserService $companyUserService,
    ): RedirectResponse {
        $user = $request->user();
        abort_if(! $user, 403);
        abort_if(! $user->can('manageCompanies', Company::class), 403);

        $managedUser = $companyUserService->findUserWithCompanies($managedUser->id);
        abort_if(! $managedUser, 404);

        $validated = $request->validated();
        $selectedCompanyIds = collect($validated['company_ids'] ?? [])
            ->map(fn (mixed $companyId): int => (int) $companyId)
            ->unique()
            ->values();

        $roles = (array) ($validated['roles'] ?? []);
        $statuses = (array) ($validated['statuses'] ?? []);

        $assignments = $selectedCompanyIds
            ->mapWithKeys(function (int $companyId) use ($roles, $statuses): array {
                return [
                    $companyId => [
                        'role' => (string) ($roles[$companyId] ?? RoleEnum::CLIENT->value),
                        'status' => (string) ($statuses[$companyId] ?? 'active'),
                    ],
                ];
            })
            ->all();

        $companyUserService->syncUserAssignments($managedUser, $assignments);

        return redirect()->route('users.index')->with('status', 'Asignaciones de empresa actualizadas correctamente.');
    }
}
