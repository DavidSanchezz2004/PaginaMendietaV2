<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('afpnet_usuario')->nullable()->after('clave_sol');
            $table->string('afpnet_clave')->nullable()->after('afpnet_usuario');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['afpnet_usuario', 'afpnet_clave']);
        });
    }
};