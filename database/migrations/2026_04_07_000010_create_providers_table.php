<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_documento', 2)->default('6'); // 6=RUC, 1=DNI, 4=CE
            $table->string('numero_documento', 20)->index();
            $table->string('nombre_razon_social', 200);
            $table->string('nombre_comercial', 200)->nullable();
            $table->string('direccion', 300)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 120)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'numero_documento']);
            $table->index(['company_id', 'nombre_razon_social']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
