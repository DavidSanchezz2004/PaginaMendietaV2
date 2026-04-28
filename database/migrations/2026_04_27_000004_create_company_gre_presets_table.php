<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_gre_presets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('partida_ubigeo', 10)->nullable();
            $table->string('partida_direccion', 300)->nullable();
            $table->string('llegada_ubigeo', 10)->nullable();
            $table->string('modalidad', 2)->default('02');
            $table->string('unidad_peso', 5)->default('KGM');
            $table->string('placa', 10)->nullable();
            $table->string('conductor_dni', 20)->nullable();
            $table->string('conductor_nombre', 100)->nullable();
            $table->string('conductor_apellido', 100)->nullable();
            $table->string('conductor_licencia', 20)->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_gre_presets');
    }
};
