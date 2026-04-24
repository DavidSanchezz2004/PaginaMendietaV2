<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Reemplazar monto_retencion simple por estructura detallada
            
            // Si no existen los campos, los agregamos
            if (!Schema::hasColumn('purchases', 'retention_enabled')) {
                $table->boolean('retention_enabled')->default(false)->after('monto_retencion');
            }
            
            if (!Schema::hasColumn('purchases', 'retention_base')) {
                $table->decimal('retention_base', 14, 2)->nullable()->after('retention_enabled');
            }
            
            if (!Schema::hasColumn('purchases', 'retention_percentage')) {
                $table->decimal('retention_percentage', 5, 2)->nullable()->after('retention_base');
            }
            
            if (!Schema::hasColumn('purchases', 'retention_amount')) {
                $table->decimal('retention_amount', 14, 2)->nullable()->after('retention_percentage');
            }
            
            if (!Schema::hasColumn('purchases', 'net_total')) {
                $table->decimal('net_total', 14, 2)->nullable()->after('retention_amount');
            }
            
            if (!Schema::hasColumn('purchases', 'retention_info')) {
                $table->json('retention_info')->nullable()->after('net_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumnIfExists('retention_enabled');
            $table->dropColumnIfExists('retention_base');
            $table->dropColumnIfExists('retention_percentage');
            $table->dropColumnIfExists('retention_amount');
            $table->dropColumnIfExists('net_total');
            $table->dropColumnIfExists('retention_info');
        });
    }
};
