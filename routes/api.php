<?php

use App\Http\Controllers\Api\CompraApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Facturador Compras
|--------------------------------------------------------------------------
| Autenticación: Bearer token estático por empresa (companies.api_token).
| Middleware 'api.token' valida el token e inyecta la empresa en el request.
|
| Configurar en n8n:
|   Header: Authorization: Bearer {api_token}
|   Base URL: https://tu-dominio.com/api
*/

// ── Generar api_token (bootstrap, sin autenticación previa) ──────────────
// Solo para setup inicial. En producción proteger con IP allowlist.
Route::post('/empresa/generar-token', [CompraApiController::class, 'generarToken'])
    ->name('api.empresa.generar-token');

Route::middleware('api.token')->group(function (): void {

    // ── Importar comprobante desde n8n / IA ──────────────────────────────
    // Recibe el JSON extraído del PDF, lo valida, asigna cuentas y guarda.
    Route::post('/compras/importar', [CompraApiController::class, 'importar'])
        ->name('api.compras.importar');

    // ── Solo validar sin guardar (útil para preview en n8n) ───────────────
    Route::post('/compras/validar', [CompraApiController::class, 'validar'])
        ->name('api.compras.validar');

    // ── Canjear compra a letras de cambio ────────────────────────────────
    Route::post('/compras/{purchase}/canjear-letras', [CompraApiController::class, 'canjeLetras'])
        ->name('api.compras.canjear-letras');

    // ── Exportar libro de compras Excel ──────────────────────────────────
    // Query params: from=2026-04-01&to=2026-04-30
    Route::get('/compras/exportar-excel', [CompraApiController::class, 'exportarExcel'])
        ->name('api.compras.exportar-excel');
});
