<?php

namespace App\Services\Company;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyManagementRepositoryInterface;
use App\Services\External\RucLookupService;
use Illuminate\Support\Collection;

class CompanyManagementService
{
    public function __construct(
        private readonly CompanyManagementRepositoryInterface $companyManagementRepository,
        private readonly RucLookupService $rucLookupService,
    ) {
    }

    /**
     * @return Collection<int, Company>
     */
    public function listUserCompanies(User $user): Collection
    {
        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        
        if (in_array($role, ['admin', 'supervisor'], true)) {
            return $this->companyManagementRepository->allCompanies();
        }

        return $this->companyManagementRepository->activeCompaniesByUser($user->id);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function createCompany(User $ownerUser, array $validated): Company
    {
        $company = $this->companyManagementRepository->create([
            'ruc' => (string) $validated['ruc'],
            'name' => (string) $validated['name'],
            'status' => (string) ($validated['status'] ?? 'active'),
            'facturador_enabled' => (bool) ($validated['facturador_enabled'] ?? false),
        ]);

        $ownerUser->companies()->syncWithoutDetaching([
            $company->id => [
                'role' => RoleEnum::ADMIN,
                'status' => 'active',
            ],
        ]);

        return $company;
    }

    public function updateCompany(Company $company, array $validated): Company
    {
        return $this->companyManagementRepository->update($company, [
            'ruc' => (string) $validated['ruc'],
            'name' => (string) $validated['name'],
            'status' => (string) ($validated['status'] ?? 'active'),
            'facturador_enabled' => (bool) ($validated['facturador_enabled'] ?? false),
        ]);
    }

    public function deleteCompany(Company $company): bool
    {
        return $this->companyManagementRepository->delete($company);
    }

    /**
     * @return array<string, string>
     */
    public function lookupRuc(string $ruc): array
    {
        $data = $this->rucLookupService->lookup($ruc);

        return [
            'ruc' => $data['ruc'],
            'name' => $data['name'],
            'direccion' => $data['direccion'],
            'ubicacion' => implode(' - ', array_filter([
                $data['departamento'],
                $data['provincia'],
                $data['distrito'],
            ])),
            'status' => mb_strtoupper($data['estado']) === 'ACTIVO' ? 'active' : 'inactive',
        ];
    }
}
