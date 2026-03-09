<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige el precision de columnas decimal en invoice_items.
 *
 * El tipo decimal(14,10) solo permite 4 dígitos enteros (máx. 9999.99…).
 * Los montos reales pueden superar ese límite, por lo que se cambia a
 * decimal(14,4): 10 dígitos enteros y 4 decimales.
 *
 * Columnas afectadas:
 *   - monto_valor_unitario  (decimal 14,10 → 14,4)
 *   - monto_precio_unitario (decimal 14,10 → 14,4)
 *   - monto_valor_total     (decimal 14,10 → 14,4)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->decimal('monto_valor_unitario', 14, 4)->comment('Sin IGV')->change();
            $table->decimal('monto_precio_unitario', 14, 4)->comment('Con IGV')->change();
            $table->decimal('monto_valor_total', 14, 4)->comment('cantidad * valor_unitario')->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->decimal('monto_valor_unitario', 14, 10)->comment('Sin IGV')->change();
            $table->decimal('monto_precio_unitario', 14, 10)->comment('Con IGV')->change();
            $table->decimal('monto_valor_total', 14, 10)->comment('cantidad * valor_unitario')->change();
        });
    }
};
