<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Company;
use App\Services\Auth\AuthService;
use App\Services\Company\ActiveCompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.auth');
    }

    public function store(
        LoginRequest $request,
        AuthService $authService,
        ActiveCompanyService $activeCompanyService,
    ): RedirectResponse
    {
        $request->authenticate($authService);

        $request->session()->regenerate();

        $user = $request->user();
        $activeCompany = $user
            ? $activeCompanyService->initializeForUser($user, $request->session())
            : null;

        if (! $user) {
            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'No se pudo iniciar sesión. Intenta nuevamente.',
            ]);
        }

        if (! $activeCompany) {
            if ($user->can('manageCompanies', Company::class)) {
                return redirect()
                    ->route('companies.create')
                    ->with('status', 'No tienes una empresa activa. Registra una empresa para continuar.');
            }

            auth()->guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'No tienes una empresa activa asignada. Contacta al administrador.',
            ]);
        }

        return redirect()->route($authService->dashboardRouteForAuthenticatedUser());
    }

    public function dashboard(AuthService $authService): RedirectResponse
    {
        return redirect()->route($authService->dashboardRouteForAuthenticatedUser());
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
