<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letras_cambio', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained('purchases')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            // Identificación de la letra
            $table->string('numero_letra', 20);  // e.g. "104-26"
            $table->string('referencia', 30)->nullable(); // e.g. "E001-2326"

            // Girador (tenedor — quien cobra)
            $table->string('tenedor_nombre', 200);
            $table->string('tenedor_ruc', 20)->nullable();
            $table->string('tenedor_domicilio', 300)->nullable();

            // Aceptante (quien paga)
            $table->string('aceptante_nombre', 200);
            $table->string('aceptante_ruc', 20)->nullable();
            $table->string('aceptante_domicilio', 300)->nullable();
            $table->string('aceptante_telefono', 30)->nullable();
            $table->string('aceptante_representante', 200)->nullable();
            $table->string('aceptante_doi', 20)->nullable();

            // Información del giro
            $table->string('lugar_giro', 100)->default('LIMA');
            $table->date('fecha_giro');
            $table->date('fecha_vencimiento');

            // Importe
            $table->string('codigo_moneda', 3)->default('PEN');
            $table->decimal('monto', 15, 2);
            $table->string('monto_letras', 300)->nullable(); // "Diez Mil..."

            // Datos bancarios (para débito en cuenta)
            $table->string('banco', 100)->nullable();
            $table->string('banco_oficina', 50)->nullable();
            $table->string('banco_cuenta', 50)->nullable();
            $table->string('banco_dc', 10)->nullable();

            // Contable
            $table->string('cuenta_contable', 10)->default('4201'); // Letras por pagar

            // Estado y saldo
            $table->string('estado', 20)->default('pendiente'); // pendiente|cobrado|protestado
            $table->decimal('monto_pagado', 15, 2)->default(0);

            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'estado']);
            $table->index(['company_id', 'fecha_vencimiento']);
            $table->index(['company_id', 'purchase_id']);
        });

        Schema::create('pagos_letras', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letra_cambio_id')->constrained('letras_cambio')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->date('fecha_pago');
            $table->decimal('monto_pagado', 15, 2);
            $table->string('medio_pago', 30)->default('transferencia'); // efectivo|transferencia|cheque|yape
            $table->string('referencia_pago', 100)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();

            $table->index(['letra_cambio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_letras');
        Schema::dropIfExists('letras_cambio');
    }
};
