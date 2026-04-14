<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega gre_transportista para Guías de Remisión con modalidad 01 (Transporte Público).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->json('gre_transportista')->nullable()
                ->comment('{"codigo_tipo_documento_transportista":"6","numero_documento_transportista":"...","nombre_razon_social_transportista":"..."}')
                ->after('gre_conductores');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn('gre_transportista');
        });
    }
};
