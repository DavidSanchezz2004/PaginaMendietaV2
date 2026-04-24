<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();

            // Identificación del comprobante recibido
            $table->string('codigo_tipo_documento', 2)->default('01'); // 01=Factura, 03=Boleta, 07=N.Crédito, 08=N.Débito, 00=DUA
            $table->string('serie_documento', 10)->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();

            // Proveedor (datos desnormalizados para histórico)
            $table->string('tipo_doc_proveedor', 2)->default('6');
            $table->string('numero_doc_proveedor', 20);
            $table->string('razon_social_proveedor', 200);

            // Moneda y cambio
            $table->string('codigo_moneda', 3)->default('PEN');
            $table->decimal('monto_tipo_cambio', 10, 4)->nullable();
            $table->unsignedSmallInteger('porcentaje_igv')->default(18);

            // Importes
            $table->decimal('base_imponible_gravadas', 15, 2)->default(0);
            $table->decimal('igv_gravadas', 15, 2)->default(0);
            $table->decimal('monto_no_gravado', 15, 2)->default(0);   // inafectas
            $table->decimal('monto_exonerado', 15, 2)->default(0);
            $table->decimal('monto_exportacion', 15, 2)->default(0);
            $table->decimal('monto_isc', 15, 2)->default(0);
            $table->decimal('monto_icbper', 15, 2)->default(0);
            $table->decimal('otros_tributos', 15, 2)->default(0);
            $table->decimal('monto_descuento', 15, 2)->default(0);
            $table->decimal('monto_total', 15, 2)->default(0);

            // Forma de pago y cuotas
            $table->string('forma_pago', 2)->nullable(); // 01=Contado, 02=Crédito
            $table->json('lista_cuotas')->nullable();

            // Nota / nota crédito / DUA
            $table->string('anio_emision_dua', 4)->nullable(); // Solo para DUA (tipo 00)
            $table->date('fecha_doc_modifica')->nullable();
            $table->string('tipo_doc_modifica', 2)->nullable();
            $table->string('serie_doc_modifica', 10)->nullable();
            $table->string('numero_doc_modifica', 20)->nullable();
            $table->string('tipo_nota', 4)->nullable();

            // Campos de clasificación COMPRAS
            $table->string('tipo_compra', 4)->nullable(); // NI, NG, EX, GR, MX
            $table->string('tipo_operacion', 10)->nullable(); // 0401, 0409, 0412…

            // Campos contables
            $table->string('cuenta_contable', 10)->nullable();
            $table->string('codigo_producto_servicio', 50)->nullable();
            $table->string('glosa', 500)->nullable();
            $table->string('centro_costo', 50)->nullable();
            $table->string('tipo_gasto', 20)->nullable();
            $table->string('sucursal', 50)->nullable();
            $table->string('comprador', 100)->nullable();

            // Flags booleanos
            $table->boolean('es_anticipo')->default(false);
            $table->boolean('es_documento_contingencia')->default(false);
            $table->boolean('es_sujeto_detraccion')->default(false);
            $table->boolean('es_sujeto_retencion')->default(false);
            $table->boolean('es_sujeto_percepcion')->default(false);

            // Estado contable
            $table->string('accounting_status', 20)->default('incompleto'); // incompleto, pendiente, listo

            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'fecha_emision']);
            $table->index(['company_id', 'accounting_status']);
            $table->index(['company_id', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};