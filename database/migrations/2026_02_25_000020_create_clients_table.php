<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de clientes/receptores por empresa (multiempresa estricto).
 * Scoped: unique(company_id, codigo_tipo_documento, numero_documento).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            // Identificación SUNAT (tipo: 1=DNI, 6=RUC, 4=Carnet extranjería, etc.)
            $table->string('codigo_tipo_documento', 2)
                ->comment('1=DNI, 4=Carnet, 6=RUC, 7=Pasaporte');
            $table->string('numero_documento', 20);
            $table->string('nombre_razon_social');

            // Datos de ubicación (opcionales, usados en comprobante)
            $table->char('codigo_pais', 2)->default('PE');
            $table->string('ubigeo', 10)->nullable();
            $table->string('departamento', 100)->nullable();
            $table->string('provincia', 100)->nullable();
            $table->string('distrito', 100)->nullable();
            $table->string('urbanizacion', 100)->nullable();
            $table->string('direccion')->nullable();
            $table->string('correo', 200)->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            // Anti-IDOR: misma empresa no puede tener el mismo doc del mismo tipo
            $table->unique(
                ['company_id', 'codigo_tipo_documento', 'numero_documento'],
                'clients_company_tipo_doc_unique'
            );
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
