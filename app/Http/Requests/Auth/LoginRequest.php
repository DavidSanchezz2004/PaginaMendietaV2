<?php

namespace App\Http\Requests\Auth;

use App\Services\Auth\AuthService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $hasRecaptcha = (string) config('services.recaptcha.site_key', '') !== '';

        return [
            'email'           => ['required', 'string', 'email', 'max:255'],
            'password'        => ['required', 'string', 'min:8', 'max:255'],
            'recaptcha_token' => $hasRecaptcha ? ['required', 'string'] : ['nullable', 'string'],
        ];
    }

    /**
     * Hooks extra de validación: verificar el token con Google.
     */
    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v): void {
            // Si ya hay errores de campo, no llamamos a Google innecesariamente.
            if ($v->errors()->isNotEmpty()) {
                return;
            }

            $token     = (string) $this->input('recaptcha_token', '');
            $secretKey = (string) config('services.recaptcha.secret_key', '');
            $threshold = (float)  config('services.recaptcha.threshold', 0.5);

            // Si no hay clave configurada, se omite la verificación.
            if ($secretKey === '') {
                return;
            }

            try {
                $response = Http::asForm()->post(
                    'https://www.google.com/recaptcha/api/siteverify',
                    ['secret' => $secretKey, 'response' => $token]
                );

                $data = $response->json();

                if (
                    ! ($data['success'] ?? false)
                    || ($data['action']  ?? '') !== 'login'
                    || ($data['score']   ?? 0.0) < $threshold
                ) {
                    Log::warning('reCAPTCHA v3 falló', [
                        'ip'    => $this->ip(),
                        'data'  => $data,
                    ]);

                    $v->errors()->add(
                        'recaptcha_token',
                        'No pudimos verificar que eres humano. Inténtalo de nuevo.'
                    );
                }
            } catch (\Throwable $e) {
                Log::error('Error al verificar reCAPTCHA', ['error' => $e->getMessage()]);
                // En caso de fallo del servicio externo, permitimos el intento
                // para no bloquear usuarios legítimos por caídas de Google.
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(AuthService $authService): void
    {
        $this->ensureIsNotRateLimited();

        $authenticated = $authService->authenticate(
            email: (string) $this->string('email'),
            password: (string) $this->string('password'),
        );

        if (! $authenticated) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => RateLimiter::availableIn($this->throttleKey()),
                'minutes' => ceil(RateLimiter::availableIn($this->throttleKey()) / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower((string) $this->string('email')).'|'.$this->ip());
    }
}
