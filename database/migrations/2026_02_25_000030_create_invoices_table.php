<?php

use App\Enums\InvoiceStatusEnum;
use App\Enums\FeasyStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Facturas emitidas por empresa (multiempresa estricto).
 * Cada fila está scoped a company_id = session('company_id').
 * Incluye todos los campos de trazabilidad Feasy/SUNAT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();

            // ── Scoping multiempresa ──────────────────────────────────────────
            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->comment('Operador interno que creó/emitió la factura')
                ->constrained('users');

            $table->foreignId('client_id')
                ->constrained('clients');

            // ── Información del documento (mapeo directo a Feasy) ─────────────
            $table->string('codigo_interno', 30)
                ->comment('Código interno: ej. 01F00100000001');
            $table->date('fecha_emision');
            $table->time('hora_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->char('forma_pago', 2)->comment('1=Contado, 2=Crédito');

            $table->string('codigo_tipo_documento', 2)
                ->comment('01=Factura, 03=Boleta, 07=N.Crédito, 08=N.Débito');
            $table->string('serie_documento', 5)->comment('Ej: F001, B001');
            $table->string('numero_documento', 10)->comment('Ej: 00000001');

            $table->string('observacion')->nullable();
            $table->string('correo', 200)->nullable();
            $table->string('numero_orden_compra', 50)->nullable();
            $table->char('codigo_moneda', 3)->default('PEN');
            $table->decimal('porcentaje_igv', 5, 2)->default(18.00);
            $table->decimal('monto_tipo_cambio', 12, 4)->nullable();

            // ── Montos (todos requeridos por SUNAT) ───────────────────────────
            $table->decimal('monto_total_anticipo', 14, 2)->nullable();
            $table->decimal('monto_total_gravado', 14, 2)->default(0);
            $table->decimal('monto_total_inafecto', 14, 2)->nullable();
            $table->decimal('monto_total_exonerado', 14, 2)->nullable();
            $table->decimal('monto_total_exportacion', 14, 2)->nullable();
            $table->decimal('monto_total_descuento', 14, 2)->nullable();
            $table->decimal('monto_total_isc', 14, 2)->nullable();
            $table->decimal('monto_total_igv', 14, 2)->default(0);
            $table->decimal('monto_total_impuesto_bolsa', 14, 2)->nullable();
            $table->decimal('monto_total_gratuito', 14, 2)->nullable();
            $table->decimal('monto_total_otros_cargos', 14, 2)->nullable();
            $table->decimal('monto_total', 14, 2)->default(0);

            // ── Entrega bienes (opcional, se serializa como JSON) ─────────────
            $table->json('informacion_entrega_bienes')->nullable();
            $table->boolean('indicador_entrega_bienes')->default(false);

            // ── Estado del comprobante ────────────────────────────────────────
            $table->string('estado')->default(InvoiceStatusEnum::DRAFT->value)
                ->comment('draft|ready|sent|error|consulted');

            // ── Trazabilidad Feasy/SUNAT (campos exactos requeridos) ──────────
            $table->string('estado_feasy')->default(FeasyStatusEnum::PENDING->value)
                ->comment('pending|sent|rejected|error|consulted');
            $table->string('codigo_respuesta_sunat', 10)->nullable();
            $table->text('mensaje_respuesta_sunat')->nullable();
            $table->string('nombre_archivo_xml')->nullable();
            $table->string('xml_path')->nullable()
                ->comment('Ruta en storage privado: private/companies/{id}/xml/');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('consulted_at')->nullable();
            $table->text('last_error')->nullable()
                ->comment('JSON crudo de la última respuesta de error');

            $table->timestamps();

            // ── Índices y constraints ─────────────────────────────────────────
            $table->unique(
                ['company_id', 'codigo_tipo_documento', 'serie_documento', 'numero_documento'],
                'invoices_company_serie_numero_unique'
            );
            $table->index('company_id');
            $table->index(['company_id', 'estado']);
            $table->index(['company_id', 'fecha_emision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
