<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agregar soporte para letras de cambio en VENTAS (Invoice).
 * Mantiene backward compatibility con purchase_id (compras aún funcionan).
 * 
 * Cambio de arquitectura: Letras ahora pueden ser:
 * - De Compra (purchase_id != null, invoice_id = null) — Letras por pagar
 * - De Venta (invoice_id != null, purchase_id = null) — Letras por cobrar
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letras_cambio', function (Blueprint $table): void {
            // Agregar FK a invoices (para letras por cobrar)
            $table->foreignId('invoice_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('invoices')
                ->nullOnDelete()
                ->comment('Letra de venta (letras por cobrar). NULL si es de compra.');

            // Agregar índice para queries rápidas
            $table->index(['company_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::table('letras_cambio', function (Blueprint $table): void {
            $table->dropForeignKeyIfExists(['invoice_id']);
            $table->dropIndex(['company_id', 'invoice_id']);
            $table->dropColumn('invoice_id');
        });
    }
};
