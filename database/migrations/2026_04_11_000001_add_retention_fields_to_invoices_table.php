<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agregar campos de retención a facturas (Ventas).
 * Retenciones variables por cliente/tipo documento.
 *
 * Fórmula: retention_amount = retention_base * (retention_percentage / 100)
 * Impacto: net_total = total - retention_amount
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            // Habilitación de retención
            $table->boolean('retention_enabled')
                ->default(false)
                ->after('estado_feasy')
                ->comment('¿Esta factura tiene retención?');

            // Base imponible para calcular retención
            $table->decimal('retention_base', 14, 2)
                ->nullable()
                ->after('retention_enabled')
                ->comment('Base para calcular retención (puede diferir del total)');

            // Porcentaje de retención variable
            $table->decimal('retention_percentage', 5, 2)
                ->nullable()
                ->after('retention_base')
                ->comment('Porcentaje de retención (ej: 3.00 para 3%)');

            // Monto retenido calculado
            $table->decimal('retention_amount', 14, 2)
                ->nullable()
                ->after('retention_percentage')
                ->comment('Monto retenido = retention_base * (retention_percentage / 100)');

            // Total neto después de retención
            $table->decimal('net_total', 14, 2)
                ->nullable()
                ->after('retention_amount')
                ->comment('Total a pagar = monto_total - retention_amount');

            // JSON con información detallada de retención (si aplica)
            $table->json('retention_info')
                ->nullable()
                ->after('net_total')
                ->comment('Info adicional de retención: motivo, fecha, referencia, etc.');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'retention_enabled',
                'retention_base',
                'retention_percentage',
                'retention_amount',
                'net_total',
                'retention_info',
            ]);
        });
    }
};
