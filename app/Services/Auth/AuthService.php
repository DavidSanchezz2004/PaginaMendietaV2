<?php

namespace App\Services\Auth;

use App\Enums\RoleEnum;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    public function authenticate(string $email, string $password, bool $remember = false): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user) {
            return false;
        }

        return Auth::attempt([
            'email' => $email,
            'password' => $password,
        ], $remember);
    }

    public function dashboardRouteForAuthenticatedUser(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'login';
        }

        return 'dashboard';
    }
}