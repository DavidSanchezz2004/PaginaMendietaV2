<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Soporte para Guía de Remisión Electrónica (GRE) tipo "09".
 *
 * Cambios en invoices:
 *  - client_id → nullable (GRE tiene destinatario propio, no siempre registrado)
 *  - forma_pago → nullable (GRE no tiene forma de pago)
 *  - Nuevas columnas GRE: motivo, modalidad, fecha_inicio, peso, puntos, destinatario, vehículos, conductores
 *
 * Cambios en invoice_items:
 *  - tipo → nullable (ítems GRE no tienen tipo P/S)
 *  - monto_* → nullable (ítems GRE no tienen montos)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            // Hacer nullable para GRE
            $table->foreignId('client_id')->nullable()->change();
            $table->char('forma_pago', 2)->nullable()->change();

            // ── Campos GRE en informacion_documento ──────────────────────────
            $table->string('codigo_motivo_traslado', 5)->nullable()
                ->comment('01=Venta, 02=Compra, 04=Traslado entre establecimientos, etc.')
                ->after('numero_orden_compra');

            $table->string('descripcion_motivo_traslado', 200)->nullable()
                ->after('codigo_motivo_traslado');

            $table->string('codigo_modalidad_traslado', 5)->nullable()
                ->comment('01=Transporte público, 02=Transporte privado')
                ->after('descripcion_motivo_traslado');

            $table->date('fecha_inicio_traslado')->nullable()
                ->after('codigo_modalidad_traslado');

            $table->string('codigo_unidad_medida_peso_bruto', 10)->nullable()
                ->comment('KGM=Kilogramos')
                ->after('fecha_inicio_traslado');

            $table->decimal('peso_bruto_total', 10, 4)->nullable()
                ->after('codigo_unidad_medida_peso_bruto');

            // ── Secciones GRE (JSON) ──────────────────────────────────────────
            $table->json('gre_punto_partida')->nullable()
                ->comment('{"ubigeo_punto_partida":"150101","direccion_punto_partida":"Av. Lima 123"}')
                ->after('peso_bruto_total');

            $table->json('gre_punto_llegada')->nullable()
                ->after('gre_punto_partida');

            $table->json('gre_destinatario')->nullable()
                ->comment('{"codigo_tipo_documento_destinatario":"1","numero_documento_destinatario":"...","nombre_razon_social_destinatario":"..."}')
                ->after('gre_punto_llegada');

            $table->json('gre_vehiculos')->nullable()
                ->comment('[{"correlativo":1,"numero_placa":"ABC123","indicador_principal":true}]')
                ->after('gre_destinatario');

            $table->json('gre_conductores')->nullable()
                ->comment('[{"correlativo":1,"codigo_tipo_documento":"1","numero_documento":"...","nombre":"...","apellido":"...","numero_licencia":"...","indicador_principal":true}]')
                ->after('gre_vehiculos');
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            // ítems GRE no tienen tipo P/S, ni montos
            $table->char('tipo', 1)->nullable()->change();
            $table->decimal('monto_valor_unitario',  14, 10)->nullable()->change();
            $table->decimal('monto_precio_unitario', 14, 10)->nullable()->change();
            $table->decimal('monto_valor_total',     14, 10)->nullable()->change();
            $table->decimal('monto_igv',             14, 2)->nullable()->change();
            $table->decimal('monto_total',           14, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'codigo_motivo_traslado',
                'descripcion_motivo_traslado',
                'codigo_modalidad_traslado',
                'fecha_inicio_traslado',
                'codigo_unidad_medida_peso_bruto',
                'peso_bruto_total',
                'gre_punto_partida',
                'gre_punto_llegada',
                'gre_destinatario',
                'gre_vehiculos',
                'gre_conductores',
            ]);
        });
    }
};
