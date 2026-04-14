<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega campos de ubicación (departamento, provincia, distrito) a companies.
 * Necesarios para poblar departamento_emisor / provincia_emisor / distrito_emisor
 * en el payload de Feasy al emitir comprobantes electrónicos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('departamento', 100)->nullable()->after('direccion_fiscal');
            $table->string('provincia', 100)->nullable()->after('departamento');
            $table->string('distrito', 100)->nullable()->after('provincia');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['departamento', 'provincia', 'distrito']);
        });
    }
};
