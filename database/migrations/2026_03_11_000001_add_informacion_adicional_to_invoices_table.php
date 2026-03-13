<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega soporte para Información Adicional (campos libres SUNAT) en la tabla invoices.
 *
 * Feasy requiere el bloque "informacion_adicional" en el JSON de emisión:
 *   {
 *     "informacion_adicional_1": "VALOR 1",
 *     "informacion_adicional_2": "VALOR 2",
 *     ...hasta 10 campos
 *   }
 *
 * Aplica para: Factura (01), Boleta (03), y comprobantes con SPOT (detracción).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->json('informacion_adicional')
                ->nullable()
                ->after('informacion_detraccion')
                ->comment('JSON: campos adicionales libres requeridos por Feasy (informacion_adicional_1..10)');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn('informacion_adicional');
        });
    }
};
