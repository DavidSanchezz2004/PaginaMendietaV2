<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos requeridos por SUNAT/Feasy a la tabla companies.
 * facturador_enabled ya existe en la migración base.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('razon_social')->nullable()->after('name');
            $table->string('ubigeo', 10)->nullable()->after('razon_social');
            $table->string('direccion_fiscal')->nullable()->after('ubigeo');
            // Token Feasy encriptado POR EMPRESA (Anti-regla de token global)
            $table->text('feasy_token')->nullable()->after('direccion_fiscal');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['razon_social', 'ubigeo', 'direccion_fiscal', 'feasy_token']);
        });
    }
};
