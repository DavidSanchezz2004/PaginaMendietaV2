<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Auth\Access\Response;

class NewsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, News $news): bool
    {
        if ($user->role !== RoleEnum::CLIENT) {
            return true;
        }

        if ($news->is_global) {
            return true;
        }
        
        $activeCompanyId = session('active_company_id');
        return $news->company_id === $activeCompanyId;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role === RoleEnum::ADMIN || $user->role === RoleEnum::SUPERVISOR || $user->role === RoleEnum::AUXILIAR;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, News $news): bool
    {
        return $user->role === RoleEnum::ADMIN || $user->id === $news->author_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, News $news): bool
    {
        return $user->role === RoleEnum::ADMIN;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, News $news): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, News $news): bool
    {
        return false;
    }
}
