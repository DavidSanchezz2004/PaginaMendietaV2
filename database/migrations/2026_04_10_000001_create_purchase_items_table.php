<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ítems (líneas de detalle) de cada comprobante de compra.
 * company_id redundante para Anti-IDOR sin JOIN.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('correlativo')->default(1);
            $table->string('descripcion');
            $table->string('unidad_medida', 30)->nullable();

            $table->decimal('cantidad', 14, 4)->default(0);
            $table->decimal('valor_unitario', 14, 6)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('importe_venta', 14, 4)->default(0);
            $table->decimal('icbper', 14, 2)->default(0);

            $table->timestamps();

            $table->index('purchase_id');
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
