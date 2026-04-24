<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega la columna lista_guias a la tabla invoices.
 *
 * Permite adjuntar guías de remisión a una factura/boleta.
 * Estructura JSON esperada:
 *   [
 *     {"codigo_tipo_documento": "09", "serie_documento": "T001", "numero_documento": "1"},
 *     {"codigo_tipo_documento": "31", "serie_documento": "V001", "numero_documento": "1"},
 *   ]
 *
 * Códigos tipo documento:
 *   "09" = GRE Remitente (Guía de Remisión Electrónica)
 *   "31" = GRE Transportista
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->json('lista_guias')
                ->nullable()
                ->after('numero_orden_compra')
                ->comment('Guías de remisión adjuntas al comprobante (array JSON). Env. a Feasy en lista_guias.');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn('lista_guias');
        });
    }
};
