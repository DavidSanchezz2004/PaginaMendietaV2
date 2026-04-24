<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos contables a la tabla invoices.
 * Permite completar información para exportación SUNAT-ready.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            // Estado de completitud contable
            $table->enum('accounting_status', ['incompleto', 'pendiente', 'listo'])
                ->default('incompleto')
                ->after('estado_feasy')
                ->comment('Completitud para exportación contable');

            // Campos de clasificación contable
            $table->string('tipo_operacion', 10)->nullable()->after('accounting_status')
                ->comment('Catálogo SUNAT. Ej: 0101');
            $table->string('tipo_venta', 10)->nullable()->after('tipo_operacion')
                ->comment('Tipo de venta: IN=Interna, EX=Exportación, NC=Nota Crédito, etc.');
            $table->string('cuenta_contable', 10)->nullable()->after('tipo_venta')
                ->comment('Código PCGE. Ej: 121, 7011');
            $table->string('codigo_producto_servicio', 50)->nullable()->after('cuenta_contable')
                ->comment('Código producto/servicio para el libro contable');
            $table->text('glosa')->nullable()->after('codigo_producto_servicio')
                ->comment('Descripción contable (distinto de observacion interna)');

            // Campos avanzados (opcionales)
            $table->string('centro_costo', 50)->nullable()->after('glosa');
            $table->string('tipo_gasto', 20)->nullable()->after('centro_costo');
            $table->string('sucursal', 50)->nullable()->after('tipo_gasto');
            $table->string('vendedor', 100)->nullable()->after('sucursal');

            // Flags contables
            $table->boolean('es_anticipo')->default(false)->after('vendedor');
            $table->boolean('es_documento_contingencia')->default(false)->after('es_anticipo');
            $table->boolean('es_sujeto_retencion')->default(false)->after('es_documento_contingencia');
            $table->boolean('es_sujeto_percepcion')->default(false)->after('es_sujeto_retencion');

            // Índice para filtrar rápido por estado contable
            $table->index(['company_id', 'accounting_status'], 'idx_invoices_accounting_status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropIndex('idx_invoices_accounting_status');
            $table->dropColumn([
                'accounting_status',
                'tipo_operacion',
                'tipo_venta',
                'cuenta_contable',
                'codigo_producto_servicio',
                'glosa',
                'centro_costo',
                'tipo_gasto',
                'sucursal',
                'vendedor',
                'es_anticipo',
                'es_documento_contingencia',
                'es_sujeto_retencion',
                'es_sujeto_percepcion',
            ]);
        });
    }
};