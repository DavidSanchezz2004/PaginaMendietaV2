<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\Profile\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, ProfileService $profileService): View
    {
        $user = $request->user();

        abort_if(! $user, 403);

        return view('profile.perfil', $profileService->buildProfileViewData($user));
    }

    public function update(UpdateProfileRequest $request, ProfileService $profileService): RedirectResponse
    {
        $user = $request->user();

        abort_if(! $user, 403);

        $profileService->updateProfile(
            $user,
            $request->validated(),
            $request->file('avatar'),
        );

        return redirect()->route('profile')->with('status', 'Perfil actualizado correctamente.');
    }
}
