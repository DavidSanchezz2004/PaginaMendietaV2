<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface CompanyUserManagementRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function allUsers(): Collection;

    /**
     * @return Collection<int, User>
     */
    public function usersByCompany(int $companyId): Collection;

    /**
     * @return Collection<int, CompanyUser>
     */
    public function activeMemberships(): Collection;

    /**
     * @return Collection<int, Company>
     */
    public function activeCompanies(): Collection;

    public function findUserWithCompanies(int $userId): ?User;

    /**
     * @param array{name:string,email:string,role:string,password?:string} $attributes
     */
    public function updateUser(User $user, array $attributes): User;

    /**
     * @param array<int, array{role:string,status:string}> $assignments
     */
    public function syncUserCompanyAssignments(User $user, array $assignments): void;

    public function createOrUpdateGlobalUser(
        string $name,
        string $email,
        string $password,
        string $systemRole,
    ): User;

    public function createOrAttachUserToCompany(
        int $companyId,
        string $name,
        string $email,
        string $password,
        string $companyRole,
    ): User;
}
