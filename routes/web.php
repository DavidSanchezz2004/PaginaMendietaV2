<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Obligaciones\CronogramaController;
use App\Http\Controllers\Company\ActiveCompanyController;
use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Configuration\CompanyFacturadorController;
use App\Http\Controllers\Configuration\FeasyConfigController;
use App\Http\Controllers\Configuration\InformacionAdicionalConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Facturador\ClientController;
use App\Http\Controllers\Facturador\FacturadorController;
use App\Http\Controllers\Facturador\InvoiceController;
use App\Http\Controllers\Facturador\CreditDebitNoteController;
use App\Http\Controllers\Facturador\ProductController;
use App\Http\Controllers\Facturador\SunatLoginController;
use App\Http\Controllers\FinalDocumentController;
use App\Http\Controllers\ObligationController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\PortalSunat\PortalSunatController;
use App\Http\Controllers\BandejaEntrada\BandejaEntradaController;
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
	Route::get('/empresas/{company}/usuarios', [CompanyController::class, 'companyUsers'])->name('companies.users.index');
	Route::post('/empresas/{company}/usuarios', [CompanyController::class, 'assignUser'])->name('companies.users.assign');
	Route::post('/empresas/{company}/usuarios/{companyUser}/quitar', [CompanyController::class, 'removeUser'])->name('companies.users.remove');

	// ── Portal SUNAT ──────────────────────────────────────────────────────
	Route::prefix('portal-sunat')->name('portal-sunat.')->group(function (): void {
		Route::get('/',                               [PortalSunatController::class, 'index'])             ->name('index');
		Route::get('/{company}/credenciales',         [PortalSunatController::class, 'credentials'])       ->name('credentials');
		Route::put('/{company}/credenciales',         [PortalSunatController::class, 'updateCredentials']) ->name('credentials.update');
		Route::get('/{company}/abrir',                [PortalSunatController::class, 'open'])              ->name('open');
        Route::post('/{company}/ocultar',             [PortalSunatController::class, 'hideForUser'])      ->name('hide');
        Route::post('/{company}/mostrar',             [PortalSunatController::class, 'unhideForUser'])    ->name('unhide');
	});
	Route::get('/usuarios', [CompanyUserController::class, 'index'])->name('users.index');
	Route::get('/usuarios/crear', [CompanyUserController::class, 'create'])->name('users.create');
	Route::post('/usuarios', [CompanyUserController::class, 'store'])->name('users.store')->middleware('throttle:10,1');
	Route::get('/usuarios/{managedUser}/editar', [CompanyUserController::class, 'edit'])->name('users.edit');
	Route::patch('/usuarios/{managedUser}', [CompanyUserController::class, 'update'])->name('users.update')->middleware('throttle:10,1');
	Route::get('/usuarios/{managedUser}/asignaciones', [CompanyUserController::class, 'editAssignments'])->name('users.assignments.edit');
	Route::patch('/usuarios/{managedUser}/asignaciones', [CompanyUserController::class, 'updateAssignments'])->name('users.assignments.update');
	Route::post('/usuarios/{managedUser}/toggle-status', [CompanyUserController::class, 'toggleStatus'])->name('users.toggle-status');
	Route::get('/auditoria', [AuditLogController::class, 'index'])->name('admin.audit-log.index');

	Route::middleware('active.company')->group(function (): void {
	Route::get('/perfil', [ProfileController::class, 'show'])->name('profile');
	Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:10,1');

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

    // Cronograma de Obligaciones SUNAT
    Route::get('/obligaciones/cronograma', [CronogramaController::class, 'index'])->name('obligaciones.cronograma.index');
    Route::post('/obligaciones/cronograma/{company}/confirmar', [CronogramaController::class, 'confirm'])->name('obligaciones.cronograma.confirm');
    Route::post('/obligaciones/cronograma/{company}/revertir', [CronogramaController::class, 'revert'])->name('obligaciones.cronograma.revert');

    // Instancias (Credenciales) - DESHABILITADA
    // Route::resource('credentials', CredentialController::class)->except(['show']);

    // Retro-compatibilidad enlaces Menú
	Route::get('/menu/obligaciones/calendario', fn () => redirect()->route('obligations.index'))->name('menu.obligaciones.calendario');
	Route::get('/menu/obligaciones/instancias', fn () => redirect()->route('dashboard'))->name('menu.obligaciones.instancias');
	Route::get('/menu/noticias', fn () => redirect()->route('news.index'))->name('menu.noticias');
	Route::get('/menu/tutoriales', fn () => redirect()->route('tutorials.index'))->name('menu.tutoriales');
	Route::get('/menu/configuracion', fn () => response('Mock: Configuración'))->name('menu.configuracion');

    // Noticias y Tutoriales
    Route::resource('news', \App\Http\Controllers\NewsController::class)->except(['index', 'show'])->parameters(['news' => 'news']);
    Route::resource('tutorials', \App\Http\Controllers\TutorialController::class)->except(['index', 'show']);
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

            // SUNAT: extensión Chrome (v3) + fallback autosubmit (v1)
            Route::get('clients/{client}/sunat-login', [SunatLoginController::class, 'redirect'])
                ->name('clients.sunat-login');

            // Llama POST /proxy/create → devuelve { ok, url: ext-inject/{token} }
            Route::get('clients/{client}/abrir-sunat', [SunatLoginController::class, 'abrirSunat'])
                ->name('clients.abrir-sunat');

            // Facturas
            Route::resource('invoices', InvoiceController::class)
                ->except(['edit', 'update']);

            // Acciones Feasy sobre una factura
            Route::post('invoices/{invoice}/emit',    [InvoiceController::class, 'emit'])
                ->name('invoices.emit')
                ->middleware('throttle:20,1');

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

            // Notas de Crédito y Débito (oculto temporalmente)
            // Route::resource('credit-debit-notes', CreditDebitNoteController::class)
            //     ->except(['edit', 'update']);
            // Route::post('credit-debit-notes/{creditDebitNote}/emit',    [CreditDebitNoteController::class, 'emit'])
            //     ->name('credit_debit_notes.emit');
            // Route::post('credit-debit-notes/{creditDebitNote}/consult', [CreditDebitNoteController::class, 'consult'])
            //     ->name('credit_debit_notes.consult');
            // Route::get('credit-debit-notes/{creditDebitNote}/xml',      [CreditDebitNoteController::class, 'downloadXml'])
            //     ->name('credit_debit_notes.xml');

            // ── Configuración: Información Adicional (valores enviados a Feasy) ──
            Route::put('configuracion/informacion-adicional', [InformacionAdicionalConfigController::class, 'update'])
                ->name('config.informacion-adicional.update');
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

	// ── Buzón SOL ─────────────────────────────────────────────────────────
	Route::prefix('bandeja-sunat')->name('bandeja-sunat.')->group(function (): void {
		Route::get('/',                              [BandejaEntradaController::class, 'index'])       ->name('index');
		Route::post('/iniciar/{company}',            [BandejaEntradaController::class, 'iniciar'])     ->name('iniciar');
		Route::get('/mensajes/{company}',            [BandejaEntradaController::class, 'mensajes'])    ->name('mensajes');
		Route::get('/detalle/{company}/{cod}',       [BandejaEntradaController::class, 'detalle'])     ->name('detalle');
		Route::get('/documento/{company}/{cod}',     [BandejaEntradaController::class, 'documento'])   ->name('documento');
		Route::post('/sincronizar/{company}',        [BandejaEntradaController::class, 'sincronizar']) ->name('sincronizar');
		Route::get('/lista/{company}',               [BandejaEntradaController::class, 'lista'])       ->name('lista');
		Route::post('/leer/{company}/{mensaje}',     [BandejaEntradaController::class, 'marcarLeido']) ->name('leer');
		Route::get('/keywords',                      [BandejaEntradaController::class, 'keywords'])    ->name('keywords');
		Route::post('/keywords',                     [BandejaEntradaController::class, 'storeKeyword'])->name('keywords.store');
		Route::delete('/keywords/{keyword}',         [BandejaEntradaController::class, 'destroyKeyword'])->name('keywords.destroy');
	});

	Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});