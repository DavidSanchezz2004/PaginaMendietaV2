<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de bienes y servicios sujetos a detracción SPOT (Anexo 2 - SUNAT).
 * Usado para poblar el selector en el formulario de emisión.
 * Referencia: RS 183-2004/SUNAT y modificatorias.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spot_detracciones', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 5)->unique()->comment('Código SUNAT del bien/servicio');
            $table->string('descripcion', 300)->comment('Descripción oficial SUNAT');
            $table->decimal('porcentaje', 5, 2)->comment('Porcentaje de detracción vigente');
            $table->boolean('activo')->default(true)->comment('false = derogado/inactivo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spot_detracciones');
    }
};
