<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credit_debit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Tipo de comprobante: '07' = Nota Crédito, '08' = Nota Débito
            $table->string('codigo_tipo_documento', 2); // '07' o '08'
            $table->string('codigo_tipo_nota', 2); // '01', '02', '03', '04' según SUNAT
            $table->string('serie_documento', 4);
            $table->string('numero_documento', 8);
            $table->string('codigo_interno', 20)->unique();

            // Datos de la nota
            $table->dateTime('fecha_emision');
            $table->time('hora_emision')->default('00:00:00');
            $table->text('observacion')->nullable();
            $table->string('correo', 255)->nullable();

            // Totales
            $table->decimal('monto_total_gravado', 12, 2)->default(0);
            $table->decimal('monto_total_inafecto', 12, 2)->default(0);
            $table->decimal('monto_total_exonerado', 12, 2)->default(0);
            $table->decimal('monto_total_igv', 12, 2)->default(0);
            $table->decimal('monto_total', 12, 2);
            $table->decimal('porcentaje_igv', 5, 2)->default(18);

            // JSON: items de la nota
            $table->json('lista_items')->nullable();

            // JSON: datos del documento referenciado (factura/boleta original)
            $table->json('informacion_documento_referencia')->nullable();

            // Estado
            $table->enum('estado', ['draft', 'sent', 'error', 'consulted', 'voided'])->default('draft');
            $table->string('codigo_respuesta_feasy', 50)->nullable();
            $table->text('mensaje_respuesta_feasy')->nullable();
            $table->text('url_pdf_feasy')->nullable();

            // Auditoría
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['company_id', 'estado']);
            $table->index(['fecha_emision']);
            $table->index(['codigo_tipo_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_debit_notes');
    }
};