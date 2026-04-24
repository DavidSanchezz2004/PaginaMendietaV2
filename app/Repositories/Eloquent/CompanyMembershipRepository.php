<?php

namespace App\Repositories\Eloquent;

use App\Enums\RoleEnum;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyMembershipRepositoryInterface;

class CompanyMembershipRepository implements CompanyMembershipRepositoryInterface
{
    private function isGlobalUser(int $userId): bool
    {
        $user = User::select('role')->find($userId);
        if (!$user) {
            return false;
        }
        $role = $user->role instanceof RoleEnum ? $user->role->value : (string) $user->role;
        return in_array($role, ['admin', 'supervisor']);
    }

    public function belongsToActiveCompany(int $userId, int $companyId): bool
    {
        if ($this->isGlobalUser($userId)) {
            return Company::query()
                ->whereKey($companyId)
                ->where('status', 'active')
                ->exists();
        }

        return Company::query()
            ->whereKey($companyId)
            ->where('status', 'active')
            ->whereHas('companyUsers', function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->where('status', 'active');
            })
            ->exists();
    }

    public function firstActiveCompanyForUser(int $userId): ?Company
    {
        if ($this->isGlobalUser($userId)) {
            return Company::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->first();
        }

        return Company::query()
            ->where('status', 'active')
            ->whereHas('companyUsers', function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->where('status', 'active');
            })
            ->orderBy('name')
            ->first();
    }

    public function activeCompaniesForUser(int $userId)
    {
        if ($this->isGlobalUser($userId)) {
            return Company::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        return Company::query()
            ->where('status', 'active')
            ->whereHas('companyUsers', function ($query) use ($userId): void {
                $query->where('user_id', $userId)
                    ->where('status', 'active');
            })
            ->orderBy('name')
            ->get();
    }
}
