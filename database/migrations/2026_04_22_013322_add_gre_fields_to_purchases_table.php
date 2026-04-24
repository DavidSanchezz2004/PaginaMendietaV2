<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Campos de GRE (Guía de Remisión Electrónica)
            $table->string('gre_numero', 30)->nullable()->comment('Número GRE: EG07-00000103');
            $table->date('gre_fecha_inicio_traslado')->nullable()->comment('Fecha inicio de traslado');
            $table->string('gre_motivo_traslado', 50)->nullable()->comment('Motivo: Venta, Devolución, etc');
            $table->text('gre_punto_partida')->nullable()->comment('Punto de partida - Dirección completa');
            $table->text('gre_punto_llegada')->nullable()->comment('Punto de llegada - Dirección completa');
            $table->string('gre_destinatario_ruc', 12)->nullable();
            $table->string('gre_destinatario_razon_social', 255)->nullable();
            $table->text('gre_documento_relacionado')->nullable()->comment('Factura relacionada: E001-223');
            $table->text('gre_bienes_descripcion')->nullable()->comment('Descripción de bienes a transportar');
            $table->integer('gre_cantidad_bienes')->nullable();
            $table->string('gre_unidad_medida', 30)->nullable()->comment('MILLARES, KG, etc');
            $table->decimal('gre_peso_bruto', 12, 2)->nullable()->comment('Peso bruto total');
            $table->string('gre_unidad_medida_peso', 10)->nullable()->comment('KGM, TNM, etc');
            $table->text('gre_datos_vehiculo')->nullable()->comment('JSON: {placa, marca, modelo, tipo}');
            $table->text('gre_datos_conductor')->nullable()->comment('JSON: {nombre, dni, licencia}');
            $table->boolean('gre_privado_transporte')->nullable()->default(false)->comment('Transporte privado');
            $table->boolean('gre_retorno_vehiculo_vacio')->nullable()->default(false);
            $table->boolean('gre_transbordo_programado')->nullable()->default(false);
            $table->text('gre_notas')->nullable()->comment('Notas adicionales de la GRE');
            $table->timestamp('gre_registrado_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'gre_numero', 'gre_fecha_inicio_traslado', 'gre_motivo_traslado',
                'gre_punto_partida', 'gre_punto_llegada',
                'gre_destinatario_ruc', 'gre_destinatario_razon_social',
                'gre_documento_relacionado', 'gre_bienes_descripcion',
                'gre_cantidad_bienes', 'gre_unidad_medida', 'gre_peso_bruto',
                'gre_unidad_medida_peso', 'gre_datos_vehiculo', 'gre_datos_conductor',
                'gre_privado_transporte', 'gre_retorno_vehiculo_vacio',
                'gre_transbordo_programado', 'gre_notas', 'gre_registrado_en',
            ]);
        });
    }
};
