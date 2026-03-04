<?php

namespace App\Repositories\Eloquent;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;
use App\Repositories\Contracts\CompanyUserManagementRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CompanyUserManagementRepository implements CompanyUserManagementRepositoryInterface
{
    public function allUsers(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->get();
    }

    public function usersByCompany(int $companyId): Collection
    {
        return User::query()
            ->whereHas('companyUsers', function ($query) use ($companyId): void {
                $query->where('company_id', $companyId)
                    ->where('status', 'active');
            })
            ->with(['companyUsers' => function ($query) use ($companyId): void {
                $query->where('company_id', $companyId);
            }])
            ->orderBy('name')
            ->get();
    }

    public function activeMemberships(): Collection
    {
        return CompanyUser::query()
            ->where('status', 'active')
            ->with([
                'user:id,name,email,role',
                'company:id,name,ruc,status',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function activeCompanies(): Collection
    {
        return Company::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function findUserWithCompanies(int $userId): ?User
    {
        return User::query()
            ->with(['companies' => function ($query): void {
                $query->select('companies.id', 'companies.name', 'companies.ruc', 'companies.status')
                    ->withPivot(['role', 'status']);
            }])
            ->find($userId);
    }

    public function updateUser(User $user, array $attributes): User
    {
        $user->fill($attributes);
        $user->save();

        return $user->refresh();
    }

    public function syncUserCompanyAssignments(User $user, array $assignments): void
    {
        $user->companies()->sync($assignments);
    }

    public function createOrUpdateGlobalUser(
        string $name,
        string $email,
        string $password,
        string $systemRole,
    ): User {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => $systemRole,
            ],
        );

        if (! $user->wasRecentlyCreated) {
            $user->forceFill([
                'name' => $name,
                'password' => $password,
                'role' => $systemRole,
            ])->save();
        }

        return $user->refresh();
    }

    public function createOrAttachUserToCompany(
        int $companyId,
        string $name,
        string $email,
        string $password,
        string $companyRole,
    ): User {
        return DB::transaction(function () use ($companyId, $name, $email, $password, $companyRole): User {
            $company = Company::query()->findOrFail($companyId);

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'role' => $companyRole,
                ],
            );

            if (! $user->wasRecentlyCreated) {
                $user->forceFill([
                    'name' => $name,
                    'role' => $companyRole,
                ])->save();
            }

            $user->companies()->syncWithoutDetaching([
                $company->id => [
                    'role' => $companyRole,
                    'status' => 'active',
                ],
            ]);

            return $user->fresh() ?? $user;
        });
    }
}