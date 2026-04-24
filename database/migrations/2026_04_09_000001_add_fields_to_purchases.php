<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            // Importes de detracción y retención (para cálculo de letra neta)
            $table->decimal('monto_detraccion', 15, 2)->default(0)->after('monto_total');
            $table->decimal('monto_retencion', 15, 2)->default(0)->after('monto_detraccion');
            // JSON con lista de errores de validación automática
            $table->json('errores_validacion')->nullable()->after('observacion');
            // Cuenta IGV asignada por regla contable
            $table->string('cuenta_igv', 10)->nullable()->after('cuenta_contable');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropColumn(['monto_detraccion', 'monto_retencion', 'errores_validacion', 'cuenta_igv']);
        });
    }
};
