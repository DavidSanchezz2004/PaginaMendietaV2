<?php

namespace App\Services\Profile;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Models\UserProfile;
use App\Repositories\Contracts\UserProfileRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(
        private readonly UserProfileRepositoryInterface $userProfileRepository,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function buildProfileViewData(User $user): array
    {
        $profile = $this->userProfileRepository->findByUserId($user->id);

        $displayName = $user->name;
        $nameParts = preg_split('/\s+/', trim($displayName)) ?: [];
        $lastName = count($nameParts) > 1 ? array_pop($nameParts) : 'No registrado';
        $firstNames = count($nameParts) > 0 ? implode(' ', $nameParts) : $displayName;

        $roleValue = $user->role instanceof RoleEnum
            ? $user->role->value
            : (string) $user->role;

        $roleLabel = match ($roleValue) {
            RoleEnum::ADMIN->value => 'Administrador',
            RoleEnum::SUPERVISOR->value => 'Supervisor',
            RoleEnum::AUXILIAR->value => 'Auxiliar',
            RoleEnum::ACCOUNTANT->value => 'Contador',
            RoleEnum::CLIENT->value, 'cliente' => 'Cliente',
            default => ucfirst($roleValue),
        };

        $profileSubtitle = match ($roleValue) {
            RoleEnum::ADMIN->value => 'Administrador del sistema',
            RoleEnum::SUPERVISOR->value => 'Supervisor de operaciones',
            RoleEnum::AUXILIAR->value => 'Auxiliar operativo',
            RoleEnum::ACCOUNTANT->value => 'Contador',
            RoleEnum::CLIENT->value, 'cliente' => 'Cliente',
            default => 'Usuario del portal',
        };

        return [
            'user' => $user,
            'profile' => $profile,
            'displayName' => $displayName,
            'firstNames' => $firstNames,
            'lastName' => $lastName,
            'roleLabel' => $roleLabel,
            'profileSubtitle' => $profileSubtitle,
            'documentNumber' => $profile?->document_number,
            'phone' => $profile?->phone,
            'address' => $profile?->address,
            'avatarUrl' => $this->resolveAvatarUrl($user, $profile),
        ];
    }

    /**
     * @param array<string, mixed> $validatedData
     */
    public function updateProfile(User $user, array $validatedData, ?UploadedFile $avatarFile): UserProfile
    {
        $profile = $this->userProfileRepository->findByUserId($user->id);

        $firstNames = trim((string) ($validatedData['first_names'] ?? ''));
        $lastNames = trim((string) ($validatedData['last_names'] ?? ''));
        $fullName = trim($firstNames.' '.$lastNames);

        if ($fullName !== '') {
            $user->forceFill([
                'name' => $fullName,
            ])->save();
        }

        $attributes = [
            'document_number' => $validatedData['document_number'] ?? null,
            'phone' => $validatedData['phone'] ?? null,
            'address' => $validatedData['address'] ?? null,
        ];

        if ($avatarFile) {
            if ($profile?->avatar_path) {
                Storage::disk('public')->delete($profile->avatar_path);
            }

            $attributes['avatar_path'] = $avatarFile->store('profile-avatars', 'public');
        }

        return $this->userProfileRepository->upsertForUser($user, $attributes);
    }

    private function resolveAvatarUrl(User $user, ?UserProfile $profile): string
    {
        if ($profile?->avatar_path) {
            return asset('storage/'.$profile->avatar_path);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=34675C&color=ffffff';
    }
}
