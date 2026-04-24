<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega soporte SPOT (Sistema de Pago de Obligaciones Tributarias / Detracciones)
 * directamente en la tabla invoices, siguiendo el mismo patrón que entrega_bienes.
 *
 *  - indicador_detraccion        → true si la factura está sujeta a detracción
 *  - informacion_detraccion      → JSON con los campos requeridos por Feasy/SUNAT:
 *                                    monto_detraccion
 *                                    porcentaje_detraccion
 *                                    codigo_bbss_sujeto_detraccion
 *                                    cuenta_banco_detraccion
 *                                    codigo_medio_pago_detraccion
 *
 * Regla SUNAT: solo aplica a Facturas (01) con monto total > 700 PEN.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->boolean('indicador_detraccion')
                ->default(false)
                ->after('indicador_entrega_bienes')
                ->comment('true = Factura sujeta a detracción SPOT');

            $table->json('informacion_detraccion')
                ->nullable()
                ->after('indicador_detraccion')
                ->comment('JSON: monto, porcentaje, codigo_bbss, cuenta_bn, medio_pago');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn(['indicador_detraccion', 'informacion_detraccion']);
        });
    }
};
