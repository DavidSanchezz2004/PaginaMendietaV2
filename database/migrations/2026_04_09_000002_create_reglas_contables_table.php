<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reglas_contables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Nombre descriptivo para la regla (interno, solo para UI)
            $table->string('nombre', 100);

            // Prioridad de matching: menor número = se evalúa primero
            $table->unsignedSmallInteger('prioridad')->default(100);

            // ── Criterios de matching (todos opcionales, se aplican en AND) ──
            // Coincide si el purchase.provider_id es este proveedor
            $table->foreignId('proveedor_id')->nullable()->constrained('providers')->nullOnDelete();
            // O si el RUC del proveedor coincide (permite reglas sin proveedor en catálogo)
            $table->string('ruc_proveedor', 20)->nullable();
            // LIKE match en glosa/descripción del comprobante
            $table->string('keyword_glosa', 100)->nullable();
            // Tipo de documento del comprobante (01, 03…)
            $table->string('tipo_documento', 2)->nullable();

            // ── Valores a asignar cuando la regla aplica ──
            $table->string('cuenta_gasto', 10)->nullable();
            $table->string('cuenta_igv', 10)->nullable()->default('40111');
            $table->string('tipo_compra', 4)->nullable();        // NG, NI, EX…
            $table->string('tipo_operacion', 10)->nullable();    // 0401…
            $table->string('tipo_gasto', 20)->nullable();
            $table->string('codigo_producto_servicio', 50)->nullable();
            $table->string('centro_costo', 50)->nullable();

            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'activo', 'prioridad']);
            $table->index(['company_id', 'proveedor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglas_contables');
    }
};
