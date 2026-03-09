<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Repositories\Contracts\CompanyManagementRepositoryInterface;
use Illuminate\Support\Collection;

class CompanyManagementRepository implements CompanyManagementRepositoryInterface
{
    public function allCompanies(): Collection
    {
        return Company::query()
            ->orderBy('name')
            ->get();
    }

    public function activeCompaniesByUser(int $userId): Collection
    {
        return Company::query()
            ->whereHas('companyUsers', function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->where('status', 'active');
            })
            ->orderBy('name')
            ->get();
    }

    public function findByUser(int $userId, int $companyId): ?Company
    {
        return Company::query()
            ->whereKey($companyId)
            ->whereHas('companyUsers', function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->where('status', 'active');
            })
            ->first();
    }

    public function create(array $attributes): Company
    {
        return Company::query()->create($attributes);
    }

    public function update(Company $company, array $attributes): Company
    {
        $company->fill($attributes);
        $company->save();

        return $company->refresh();
    }

    public function delete(Company $company): bool
    {
        return (bool) $company->delete();
    }
}
