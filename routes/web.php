<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Company\ActiveCompanyController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Configuration\CompanyFacturadorController;
use App\Http\Controllers\Configuration\FeasyConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Facturador\ClientController;
use App\Http\Controllers\Facturador\FacturadorController;
use App\Http\Controllers\Facturador\InvoiceController;
use App\Http\Controllers\Facturador\ProductController;
use App\Http\Controllers\Facturador\SunatLoginController;
use App\Http\Controllers\FinalDocumentController;
use App\Http\Controllers\ObligationController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\User\CompanyUserController;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function (): void {
	Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
	Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::patch('/active-company', [ActiveCompanyController::class, 'update'])->name('active-company.update');
    // Dashboard unificado
	Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

	Route::get('/empresas', [CompanyController::class, 'index'])->name('companies.index');
	Route::get('/empresas/crear', [CompanyController::class, 'create'])->name('companies.create');
	Route::get('/empresas/{company}/editar', [CompanyController::class, 'edit'])->name('companies.edit');
	Route::post('/empresas', [CompanyController::class, 'store'])->name('companies.store');
	Route::patch('/empresas/{company}', [CompanyController::class, 'update'])->name('companies.update');
	Route::delete('/empresas/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
	Route::post('/empresas/{company}/eliminar', [CompanyController::class, 'destroy'])->name('companies.destroy.post');
	Route::post('/empresas/lookup-ruc', [CompanyController::class, 'lookupRuc'])->name('companies.lookup-ruc');
	Route::get('/usuarios', [CompanyUserController::class, 'index'])->name('users.index');
	Route::get('/usuarios/crear', [CompanyUserController::class, 'create'])->name('users.create');
	Route::post('/usuarios', [CompanyUserController::class, 'store'])->name('users.store');
	Route::get('/usuarios/{managedUser}/editar', [CompanyUserController::class, 'edit'])->name('users.edit');
	Route::patch('/usuarios/{managedUser}', [CompanyUserController::class, 'update'])->name('users.update');
	Route::get('/usuarios/{managedUser}/asignaciones', [CompanyUserController::class, 'editAssignments'])->name('users.assignments.edit');
	Route::patch('/usuarios/{managedUser}/asignaciones', [CompanyUserController::class, 'updateAssignments'])->name('users.assignments.update');

	Route::middleware('active.company')->group(function (): void {
	Route::get('/perfil', [ProfileController::class, 'show'])->name('profile');
	Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');

	Route::get('/menu/inicio', fn () => response('Mock: Inicio'))->name('menu.inicio');
	    Route::get('/noticias', [\App\Http\Controllers\NewsController::class, 'index'])->name('news.index');
    Route::get('/noticias/{news}', [\App\Http\Controllers\NewsController::class, 'show'])->name('news.show');
    Route::get('/tutoriales', [\App\Http\Controllers\TutorialController::class, 'index'])->name('tutorials.index');
    Route::get('/tutoriales/{tutorial}', [\App\Http\Controllers\TutorialController::class, 'show'])->name('tutorials.show');

    // Documentos Finales
    Route::resource('final-documents', FinalDocumentController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::get('final-documents/{final_document}/download', [FinalDocumentController::class, 'download'])->name('final-documents.download');

    // Rutas de Soporte (Tickets) bajo Active Company (si es cliente requerirá empresa activa)
    Route::resource('tickets', TicketController::class)->except(['edit', 'update', 'destroy']);
    Route::post('tickets/{ticket}/message', [TicketController::class, 'storeMessage'])->name('tickets.message.store');
    Route::patch('tickets/{ticket}/status', [TicketController::class, 'updateStatus'])->name('tickets.status.update');
	Route::get('/reportes', [\App\Http\Controllers\Report\ReportController::class, 'index'])->name('reports.index');
	Route::get('/reportes/crear', [\App\Http\Controllers\Report\ReportController::class, 'create'])->name('reports.create');
	Route::post('/reportes', [\App\Http\Controllers\Report\ReportController::class, 'store'])->name('reports.store');
	Route::get('/reportes/{report}/editar', [\App\Http\Controllers\Report\ReportController::class, 'edit'])->name('reports.edit');
	Route::patch('/reportes/{report}', [\App\Http\Controllers\Report\ReportController::class, 'update'])->name('reports.update');
	Route::delete('/reportes/{report}', [\App\Http\Controllers\Report\ReportController::class, 'destroy'])->name('reports.destroy');
	Route::get('/reportes/{report}/descargar', [\App\Http\Controllers\Report\ReportController::class, 'download'])->name('reports.download');
	Route::post('/reportes/{report}/publicar', [\App\Http\Controllers\Report\ReportController::class, 'publish'])->name('reports.publish');
	Route::post('/reportes/{report}/despublicar', [\App\Http\Controllers\Report\ReportController::class, 'unpublish'])->name('reports.unpublish');
	Route::post('/reportes/{report}/leer', [\App\Http\Controllers\Report\ReportController::class, 'trackRead'])->name('reports.track-read');
	Route::post('/reportes/{report}/valorar', [\App\Http\Controllers\Report\ReportController::class, 'trackValued'])->name('reports.track-valued');
    // Obligaciones (Calendario)
    Route::resource('obligations', ObligationController::class)->except(['show']);
    Route::patch('obligations/{obligation}/complete', [ObligationController::class, 'markAsCompleted'])->name('obligations.complete');

    // Instancias (Credenciales)
    Route::resource('credentials', CredentialController::class)->except(['show']);

    // Retro-compatibilidad enlaces Menú
	Route::get('/menu/obligaciones/calendario', fn () => redirect()->route('obligations.index'))->name('menu.obligaciones.calendario');
	Route::get('/menu/obligaciones/instancias', fn () => redirect()->route('credentials.index'))->name('menu.obligaciones.instancias');
	Route::get('/menu/noticias', fn () => redirect()->route('news.index'))->name('menu.noticias');
	Route::get('/menu/tutoriales', fn () => redirect()->route('tutorials.index'))->name('menu.tutoriales');
	Route::get('/menu/configuracion', fn () => response('Mock: Configuración'))->name('menu.configuracion');

    // Noticias y Tutoriales
    Route::resource('news', \App\Http\Controllers\NewsController::class)->parameters(['news' => 'news']);
    Route::resource('tutorials', \App\Http\Controllers\TutorialController::class);
	});

    // ══════════════════════════════════════════════════════════════════════════
    // MÓDULO FACTURADOR
    // Grupo de rutas: auth + active.company + facturador.role + facturador.enabled
    //
    // EXCEPCIÓN: la ruta index y setActiveCompany solo requieren auth
    // (son el punto de entrada para elegir empresa activa).
    // ══════════════════════════════════════════════════════════════════════════

    // ── Punto de entrada (sin empresa activa requerida) ────────────────────
    Route::get('/facturador', [FacturadorController::class, 'index'])
        ->name('facturador.index');

    Route::post('/facturador/active-company', [FacturadorController::class, 'setActiveCompany'])
        ->name('facturador.active-company');

    // ── Rutas protegidas (requieren empresa activa + rol + habilitado) ─────
    Route::middleware(['active.company', 'facturador.role', 'facturador.enabled'])
        ->prefix('facturador')
        ->name('facturador.')
        ->group(function (): void {

            // Catálogo de Productos
            Route::resource('products', ProductController::class)
                ->except(['show']);

            // Catálogo de Clientes
            Route::get('clients/lookup-doc', [ClientController::class, 'lookupDoc'])
                ->name('clients.lookup-doc');

            Route::resource('clients', ClientController::class)
                ->except(['show']);

            // SUNAT: modal con iframe vía proxy (v2) + fallback autosubmit (v1)
            Route::get('clients/{client}/sunat-login',  [SunatLoginController::class, 'redirect'])
                ->name('clients.sunat-login');
            Route::post('clients/{client}/sunat-proxy', [SunatLoginController::class, 'getProxyUrl'])
                ->name('clients.sunat-proxy');

            // Popup SUNAT: proxy de session-redirect del bot con headers ngrok.
            // Un redirect 302 normal no puede agregar headers al request del browser a ngrok,
            // así que Laravel hace el proxy: fetch server-side con ngrok headers y devuelve la respuesta.
            Route::get('clients/sunat-session/{token}', function (string $token) {
                $botUrl   = rtrim(config('services.bot_cookies.url'), '/');
                $response = Http::withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'User-Agent'                 => 'LaravelBot/1.0',
                    'x-api-key'                  => config('services.bot_cookies.key'),
                ])->get("{$botUrl}/session-redirect/{$token}");

                return response($response->body(), $response->status())
                    ->withHeaders($response->headers());
            })->name('clients.sunat-session');

            // Proxy iframe SUNAT: ruta unificada que maneja dos casos:
            // a) Token hex válido  → obtener HTML autenticado del bot.
            // b) Cualquier otra cosa → SUNAT navegó internamente sin token.
            //    Devuelve JS que usa window.parent._sunatToken para reencaminar.
            Route::get('clients/sunat-frame/{token}', function (string $token) {
                // b) Navegación interna de SUNAT (no es un token hex de 32-36 chars)
                if (! preg_match('/^[a-f0-9-]{32,36}$/', $token)) {
                    return response(
                        '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><script>
                        (function() {
                            var t = window.parent && window.parent._sunatToken;
                            if (t) {
                                var seg  = location.pathname.split("sunat-frame/")[1] || "";
                                var full = "https://e-menu.sunat.gob.pe/cl-ti-itmenu/" + seg + location.search;
                                window.location.replace(
                                    window.location.origin +
                                    "/facturador/clients/sunat-frame/" + t +
                                    "/r?url=" + encodeURIComponent(full)
                                );
                            }
                        })();
                        </script></body></html>',
                        200
                    )->header('Content-Type', 'text/html; charset=utf-8');
                }

                // a) Token válido: obtener HTML autenticado del bot.
                $botUrl      = rtrim(config('services.bot_cookies.url'), '/');
                $laravelBase = url('facturador/clients/sunat-resource');

                $response = Http::withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'User-Agent'                 => 'LaravelBot/1.0',
                ])->get("{$botUrl}/proxy/{$token}");

                $html = str_replace($botUrl, $laravelBase, $response->body());

                return response($html, 200)
                    ->header('Content-Type', 'text/html; charset=utf-8');
            })->where('token', '.*')->name('clients.sunat-frame');

            // Proxy de recursos del bot (CSS, JS, fuentes, imágenes).
            // Route::any para soportar POST (405 con GET solo).
            Route::any('clients/sunat-resource/{path}', function (string $path) {
                $botUrl      = rtrim(config('services.bot_cookies.url'), '/');
                $laravelBase = url('facturador/clients/sunat-resource');
                $url         = "{$botUrl}/{$path}";

                if (request()->getQueryString()) {
                    $url .= '?' . request()->getQueryString();
                }

                $response    = Http::withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'User-Agent'                 => 'LaravelBot/1.0',
                ])->send(request()->method(), $url, [
                    'body' => request()->getContent(),
                ]);

                $contentType = $response->header('Content-Type') ?: 'application/octet-stream';
                $body        = $response->body();

                // En archivos de texto (CSS, JS) reescribir las URLs absolutas del bot
                // para que los recursos secundarios (fuentes, imágenes) también pasen por el proxy.
                if (str_contains($contentType, 'text/css') || str_contains($contentType, 'javascript')) {
                    $body = str_replace($botUrl, $laravelBase, $body);
                }

                return response($body, $response->status())
                    ->header('Content-Type', $contentType)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', '*')
                    ->header('Access-Control-Allow-Headers', '*');
            })->where('path', '.*')->name('clients.sunat-resource');

            // Proxy de recursos asociados a un token de sesión SUNAT.
            Route::any('clients/sunat-frame/{token}/r', function (string $token) {
                $botUrl    = rtrim(config('services.bot_cookies.url'), '/');
                $targetUrl = request()->query('url');

                if (! $targetUrl) {
                    return response('Missing url', 400);
                }

                $response = Http::withHeaders([
                    'ngrok-skip-browser-warning' => 'true',
                    'User-Agent'                 => 'LaravelBot/1.0',
                ])->send(request()->method(),
                    "{$botUrl}/proxy/{$token}/r?url=" . urlencode($targetUrl),
                    ['body' => request()->getContent()]
                );

                return response($response->body(), $response->status())
                    ->header('Content-Type', $response->header('Content-Type') ?: 'text/html')
                    ->header('Access-Control-Allow-Origin', '*');
            })->name('clients.sunat-frame-resource');

            // Facturas
            Route::resource('invoices', InvoiceController::class)
                ->except(['edit', 'update']);

            // Acciones Feasy sobre una factura
            Route::post('invoices/{invoice}/emit',    [InvoiceController::class, 'emit'])
                ->name('invoices.emit');

            Route::post('invoices/{invoice}/consult', [InvoiceController::class, 'consult'])
                ->name('invoices.consult');

            Route::post('invoices/{invoice}/void',    [InvoiceController::class, 'void'])
                ->name('invoices.void');

            Route::get('invoices/{invoice}/xml',      [InvoiceController::class, 'downloadXml'])
                ->name('invoices.xml');

            // Cobros por factura
            Route::post('invoices/{invoice}/payments',            [InvoiceController::class, 'storePayment'])
                ->name('invoices.payments.store');
            Route::delete('invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'destroyPayment'])
                ->name('invoices.payments.destroy');
        });

    // ── Configuración del Facturador por empresa (solo admin interno) ──────
    // No requiere facturador.enabled (es la ruta PARA habilitarlo/deshabilitarlo)
    Route::put('/configuracion/companies/{company}/facturador', [CompanyFacturadorController::class, 'update'])
        ->name('configuracion.companies.facturador.update');

    // ── Token Feasy global (una cuenta, todas las empresas) ───────────────
    Route::get('/configuracion/feasy',  [FeasyConfigController::class, 'edit'])
        ->name('configuracion.feasy.edit');
    Route::post('/configuracion/feasy', [FeasyConfigController::class, 'update'])
        ->name('configuracion.feasy.update');

	Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});