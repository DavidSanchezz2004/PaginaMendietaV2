<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'usuario_sol')) {
                $table->string('usuario_sol')->nullable()->after('facturador_enabled');
            }
            if (! Schema::hasColumn('companies', 'clave_sol')) {
                $table->text('clave_sol')->nullable()->after('usuario_sol');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['usuario_sol', 'clave_sol']);
        });
    }
};
