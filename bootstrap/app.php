<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // EasyPanel / reverse proxy: usar IP concreta del proxy (configurable via TRUSTED_PROXIES).
        // En desarrollo se permite '*'; en producción definir la IP real del reverse proxy.
        $middleware->trustProxies(
            at: (string) env('TRUSTED_PROXIES', '127.0.0.1'),
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO,
        );

        // Excluir las rutas proxy SUNAT del CSRF (reciben requests del iframe del bot).
        $middleware->validateCsrfTokens(except: [
            'facturador/clients/sunat-frame*',
            'facturador/clients/sunat-resource*',
            'facturador/sunat-frame*',
            'facturador/sunat-resource*',
        ]);

        $middleware->alias([
            'active.company'       => \App\Http\Middleware\EnsureUserBelongsToActiveCompany::class,
            'user.active'          => \App\Http\Middleware\EnsureUserIsActive::class,
            // ── Facturador ─────────────────────────────────────────────────
            'facturador.role'      => \App\Http\Middleware\EnsureFacturadorRole::class,
            'facturador.enabled'   => \App\Http\Middleware\EnsureFacturadorEnabled::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureUserIsActive::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
