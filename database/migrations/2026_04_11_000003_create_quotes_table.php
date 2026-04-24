<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Crear tabla de cotizaciones (quotes).
 * 
 * Características:
 * - Versionado: una cotización puede tener múltiples versiones
 * - UUID público para compartir links sin auth
 * - Estado: draft → sent → accepted / rejected
 * - Convertible a Invoice
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table): void {
            $table->id();

            // Scoping multiempresa
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->comment('Usuario que creó la cotización')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();

            // Identificación
            $table->string('codigo_interno', 30)
                ->comment('Ej: 01C00100000001 (cotización interna)');
            $table->string('numero_cotizacion', 10)
                ->comment('Número secuencial por empresa');

            // Token único para compartir sin auth
            $table->string('share_token', 36)
                ->unique()
                ->comment('UUID para URL pública: /quotes/{share_token}');

            // Versioning
            $table->integer('version')->default(1)
                ->comment('Número de versión de la cotización');

            // Información del documento
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->string('observacion')->nullable();
            $table->string('correo', 200)->nullable();
            $table->string('numero_orden_compra', 50)->nullable();

            // Moneda
            $table->char('codigo_moneda', 3)->default('PEN');
            $table->decimal('porcentaje_igv', 5, 2)->default(18.00);
            $table->decimal('monto_tipo_cambio', 12, 4)->nullable()
                ->comment('Tipo de cambio al momento de crear la cotización');

            // Montos
            $table->decimal('monto_total_gravado', 14, 2)->default(0);
            $table->decimal('monto_total_igv', 14, 2)->default(0);
            $table->decimal('monto_total_descuento', 14, 2)->nullable();
            $table->decimal('monto_total', 14, 2)->default(0);

            // Estado
            $table->string('estado', 20)
                ->default('draft')
                ->comment('draft|sent|accepted|rejected');

            // Tracking
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Conversión a factura
            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->nullOnDelete()
                ->comment('Factura generada desde esta cotización (si aplica)');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->unique(['company_id', 'numero_cotizacion'])
                ->comment('Unicidad de número por empresa');
            $table->index('company_id');
            $table->index(['company_id', 'estado']);
            $table->index(['company_id', 'client_id']);
            $table->index('share_token');
        });

        // Tabla de items de cotización
        Schema::create('quote_items', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('quote_id')
                ->constrained('quotes')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete()
                ->comment('Anti-IDOR');

            // Identificación del producto/servicio
            $table->string('codigo_interno', 50)->nullable();
            $table->string('codigo_sunat', 50)->nullable();
            $table->char('tipo', 1)->comment('P=Producto, S=Servicio');
            $table->string('descripcion', 500);

            // Unidad de medida
            $table->string('codigo_unidad_medida', 10)->default('UND');

            // Cantidades y precios
            $table->decimal('cantidad', 14, 4);
            $table->decimal('monto_valor_unitario', 14, 6)
                ->comment('Precio sin IGV');
            $table->decimal('monto_precio_unitario', 14, 6)
                ->comment('Precio con IGV (si aplica)');

            // Descuentos y totales
            $table->decimal('monto_descuento', 14, 4)->nullable();
            $table->decimal('monto_valor_total', 14, 2)
                ->comment('Total sin IGV');

            // Impuestos
            $table->string('codigo_indicador_afecto', 5)
                ->default('10')
                ->comment('10=Gravado, 20=Exonerado, 21=Inafecto');
            $table->decimal('monto_igv', 14, 2)->nullable();
            $table->decimal('monto_total', 14, 2)
                ->comment('Total con impuestos');

            $table->timestamps();

            // Índices
            $table->index('quote_id');
            $table->index(['quote_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
        Schema::dropIfExists('quotes');
    }
};
