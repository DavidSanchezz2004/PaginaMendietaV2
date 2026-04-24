<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Buscar la compra #14 y agregar información de detracción si existe
        DB::table('purchases')
            ->where('id', 14)
            ->where('es_sujeto_detraccion', true)
            ->where('monto_detraccion', 19.00)
            ->update([
                'informacion_detraccion' => json_encode([
                    'leyenda' => 'Operación sujeta al Sistema de Pago de Obligaciones Tributarias con el Gobierno Central ó Servicio de Transporte de Carga',
                    'bien_codigo' => '027',
                    'bien_descripcion' => 'Servicio de transporte de carga',
                    'medio_pago' => '001 Depósito en cuenta',
                    'numero_cuenta' => '00042032913',
                    'porcentaje' => 4.00,
                ]),
                'monto_neto_detraccion' => 453.00,
            ]);
    }

    public function down(): void
    {
        // Revertir si es necesario
        DB::table('purchases')
            ->where('id', 14)
            ->update([
                'informacion_detraccion' => null,
            ]);
    }
};
