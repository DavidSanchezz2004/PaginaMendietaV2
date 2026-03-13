<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega la columna lista_cuotas a la tabla invoices.
 *
 * Se usa cuando forma_pago = '2' (Crédito).
 * Estructura JSON esperada:
 *   [
 *     {"correlativo": 1, "fecha_pago": "2023-09-03", "monto": 236.00},
 *     {"correlativo": 2, "fecha_pago": "2023-10-03", "monto": 236.00},
 *     ...
 *   ]
 *
 * Feasy recibe esto bajo: informacion_credito.lista_cuotas
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->json('lista_cuotas')
                ->nullable()
                ->after('forma_pago')
                ->comment('Cuotas de pago a crédito. Requerido por Feasy cuando forma_pago=2.');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn('lista_cuotas');
        });
    }
};
