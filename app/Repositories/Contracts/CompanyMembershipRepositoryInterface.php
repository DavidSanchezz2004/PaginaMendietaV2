<?php

namespace App\Repositories\Contracts;

use App\Models\Company;

interface CompanyMembershipRepositoryInterface
{
    public function belongsToActiveCompany(int $userId, int $companyId): bool;

    public function firstActiveCompanyForUser(int $userId): ?Company;

    /**
     * @return \Illuminate\Support\Collection<int, Company>
     */
    public function activeCompaniesForUser(int $userId);
}
