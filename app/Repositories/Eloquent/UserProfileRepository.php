<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\Contracts\UserProfileRepositoryInterface;

class UserProfileRepository implements UserProfileRepositoryInterface
{
    public function findByUserId(int $userId): ?UserProfile
    {
        return UserProfile::query()
            ->where('user_id', $userId)
            ->first();
    }

    public function upsertForUser(User $user, array $attributes): UserProfile
    {
        return UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            $attributes,
        );
    }
}
