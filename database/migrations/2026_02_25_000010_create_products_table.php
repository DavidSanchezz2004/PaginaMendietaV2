<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de productos/servicios por empresa (multiempresa estricto).
 * Scoped: unique(company_id, codigo_interno).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('codigo_interno', 50);
            $table->string('codigo_sunat', 50)->nullable();
            $table->char('tipo', 1)->comment('P=Producto, S=Servicio');
            $table->string('codigo_unidad_medida', 10)->comment('Ej: NIU, ZZ');
            $table->string('descripcion');
            $table->decimal('valor_unitario', 14, 10)->comment('Sin IGV');
            $table->decimal('precio_unitario', 14, 10)->comment('Con IGV');
            $table->string('codigo_indicador_afecto', 5)->default('10')
                ->comment('10=Gravado IGV, 20=Exonerado, 30=Inafecto, 40=Exportación');
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Anti-IDOR: no puede haber duplicado en misma empresa
            $table->unique(['company_id', 'codigo_interno'], 'products_company_codigo_unique');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};