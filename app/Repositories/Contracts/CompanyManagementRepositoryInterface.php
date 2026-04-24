<?php

namespace App\Repositories\Contracts;

use App\Models\Company;
use Illuminate\Support\Collection;

interface CompanyManagementRepositoryInterface
{
    /**
     * @return Collection<int, Company>
     */
    public function allCompanies(): Collection;

    /**
     * @return Collection<int, Company>
     */
    public function activeCompaniesByUser(int $userId): Collection;

    public function findByUser(int $userId, int $companyId): ?Company;

    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes): Company;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Company $company, array $attributes): Company;

    public function delete(Company $company): bool;
}
