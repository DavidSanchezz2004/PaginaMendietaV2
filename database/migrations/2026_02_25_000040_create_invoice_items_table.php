<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Items (líneas) de cada factura.
 * company_id redundante aquí para facilitar queries directas Anti-IDOR
 * sin necesidad de JOIN con invoices.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            // Redundante pero necesario para Anti-IDOR sin JOIN
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('correlativo');
            $table->string('codigo_interno', 50);
            $table->string('codigo_sunat', 50)->nullable();
            $table->char('tipo', 1)->comment('P=Producto, S=Servicio');
            $table->string('codigo_unidad_medida', 10);
            $table->string('descripcion');

            $table->decimal('cantidad', 14, 4);
            $table->decimal('monto_valor_unitario', 14, 4)->comment('Sin IGV');
            $table->decimal('monto_precio_unitario', 14, 4)->comment('Con IGV');
            $table->decimal('monto_descuento', 14, 2)->nullable();
            $table->decimal('monto_valor_total', 14, 4)->comment('cantidad * valor_unitario');

            $table->string('codigo_isc', 5)->nullable();
            $table->decimal('monto_isc', 14, 2)->nullable();

            $table->string('codigo_indicador_afecto', 5)->default('10');
            $table->decimal('monto_igv', 14, 2);
            $table->decimal('monto_impuesto_bolsa', 14, 2)->nullable();
            $table->decimal('monto_total', 14, 2)->comment('Total línea con IGV');

            $table->timestamps();

            $table->index('invoice_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
