<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use App\Models\UserProfile;

interface UserProfileRepositoryInterface
{
    public function findByUserId(int $userId): ?UserProfile;

    public function upsertForUser(User $user, array $attributes): UserProfile;
}
