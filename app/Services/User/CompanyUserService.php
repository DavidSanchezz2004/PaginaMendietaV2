<?php

namespace App\Services\User;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyUserManagementRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

class CompanyUserService
{
    public function __construct(
        private readonly CompanyUserManagementRepositoryInterface $companyUserManagementRepository,
    ) {
    }

    /**
     * @return Collection<int, User>
     */
    public function listGlobalUsers(): Collection
    {
        return $this->companyUserManagementRepository->allUsers();
    }

    /**
     * @return Collection<int, User>
     */
    public function listCompanyUsers(Company $company): Collection
    {
        return $this->companyUserManagementRepository->usersByCompany($company->id);
    }

    /**
     * @return Collection<int, CompanyUser>
     */
    public function listActiveMemberships(): Collection
    {
        return $this->companyUserManagementRepository->activeMemberships();
    }

    /**
     * @return Collection<int, Company>
     */
    public function listAssignableCompanies(): Collection
    {
        return $this->companyUserManagementRepository->activeCompanies();
    }

    public function findUserWithCompanies(int $userId): ?User
    {
        return $this->companyUserManagementRepository->findUserWithCompanies($userId);
    }

    /**
     * @param array{name:string,email:string,role:string,password?:string|null} $attributes
     */
    public function updateManagedUser(User $user, array $attributes): User
    {
        if (! array_key_exists('password', $attributes) || $attributes['password'] === null || $attributes['password'] === '') {
            unset($attributes['password']);
        }

        return $this->companyUserManagementRepository->updateUser($user, $attributes);
    }

    /**
     * @param array<int, array{role:string,status:string}> $assignments
     */
    public function syncUserAssignments(User $user, array $assignments): void
    {
        $this->companyUserManagementRepository->syncUserCompanyAssignments($user, $assignments);
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function createGlobalUser(array $validated): User
    {
        return $this->companyUserManagementRepository->createOrUpdateGlobalUser(
            name: (string) $validated['name'],
            email: (string) $validated['email'],
            password: Hash::make((string) $validated['password']),
            systemRole: (string) $validated['role'],
        );
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function createCompanyUser(Company $company, array $validated): User
    {
        $role = (string) ($validated['role'] ?? RoleEnum::CLIENT->value);

        return $this->companyUserManagementRepository->createOrAttachUserToCompany(
            companyId: $company->id,
            name: (string) $validated['name'],
            email: (string) $validated['email'],
            password: Hash::make((string) $validated['password']),
            companyRole: $role,
        );
    }
}
