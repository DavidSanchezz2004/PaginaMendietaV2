<?php

namespace App\Services\Company;

use App\Models\Company;
use App\Models\User;
use App\Repositories\Contracts\CompanyMembershipRepositoryInterface;
use Illuminate\Contracts\Session\Session;

class ActiveCompanyService
{
    public function __construct(
        private readonly CompanyMembershipRepositoryInterface $companyMembershipRepository,
    ) {
    }

    public function initializeForUser(User $user, Session $session): ?Company
    {
        $company = $this->companyMembershipRepository->firstActiveCompanyForUser($user->id);

        if (! $company) {
            $session->forget('company_id');

            return null;
        }

        $session->put('company_id', $company->id);

        return $company;
    }

    public function ensureValidOrInitialize(User $user, Session $session): ?Company
    {
        $activeCompanyId = $session->get('company_id');

        if (is_numeric($activeCompanyId)
            && $this->companyMembershipRepository->belongsToActiveCompany($user->id, (int) $activeCompanyId)) {
            return Company::query()->find((int) $activeCompanyId);
        }

        return $this->initializeForUser($user, $session);
    }

    public function switchCompany(User $user, int $companyId, Session $session): bool
    {
        if (! $this->companyMembershipRepository->belongsToActiveCompany($user->id, $companyId)) {
            return false;
        }

        $session->put('company_id', $companyId);

        return true;
    }
}
