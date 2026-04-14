<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            // Información completa de detracción como JSON
            $table->json('informacion_detraccion')->nullable()->after('es_sujeto_detraccion');
            // Monto neto a pagar (total - detracción)
            $table->decimal('monto_neto_detraccion', 15, 2)->nullable()->after('informacion_detraccion');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropColumn('informacion_detraccion');
            $table->dropColumn('monto_neto_detraccion');
        });
    }
};
