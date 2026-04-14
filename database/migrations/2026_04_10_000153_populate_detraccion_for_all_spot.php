<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Datos de detracción a usar
        $detraccionData = [
            'leyenda' => 'Operación sujeta al Sistema de Pago de Obligaciones Tributarias',
            'bien_codigo' => '027',
            'bien_descripcion' => 'Servicio de transporte de carga',
            'medio_pago' => 'Depósito en cuenta',
            'numero_cuenta' => null,
            'porcentaje' => null,
        ];
        
        // Obtener todas las compras SPOT sin información_detraccion
        $purchases = DB::table('purchases')
            ->where('es_sujeto_detraccion', 1)
            ->whereNull('informacion_detraccion')
            ->orWhere(function ($q) {
                $q->where('es_sujeto_detraccion', 1)->where('informacion_detraccion', '');
            })
            ->get();
        
        // Actualizar cada una con los datos de detracción
        foreach ($purchases as $purchase) {
            DB::table('purchases')
                ->where('id', $purchase->id)
                ->update([
                    'informacion_detraccion' => json_encode($detraccionData),
                    'monto_neto_detraccion' => $purchase->monto_total - $purchase->monto_detraccion,
                ]);
        }
    }

    public function down(): void
    {
        DB::table('purchases')
            ->where('es_sujeto_detraccion', 1)
            ->update([
                'informacion_detraccion' => null,
                'monto_neto_detraccion' => null,
            ]);
    }
};
