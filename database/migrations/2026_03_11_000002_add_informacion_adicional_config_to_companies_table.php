<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega la columna informacion_adicional_config a companies.
 *
 * Guarda los valores fijos por empresa que se enviarán automáticamente
 * como bloque "informacion_adicional" en el JSON a Feasy/SUNAT al emitir
 * una Factura (01), Boleta (03) o comprobante con SPOT (detracción).
 *
 * Estructura esperada en JSON:
 *   {
 *     "informacion_adicional_1": "00-0000-123456",
 *     "informacion_adicional_2": "valor2",
 *     ...
 *   }
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->json('informacion_adicional_config')
                ->nullable()
                ->after('feasy_token')
                ->comment('JSON config: valores fijos informacion_adicional_1..10 enviados a Feasy al emitir');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn('informacion_adicional_config');
        });
    }
};
