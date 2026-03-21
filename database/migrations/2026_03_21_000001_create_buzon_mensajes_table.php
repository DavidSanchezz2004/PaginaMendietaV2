<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buzon_mensajes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('cod_sunat')->comment('ID numérico devuelto por el bot (cod)');
            $table->string('asunto')->nullable();
            $table->string('remitente')->nullable();
            $table->date('fecha')->nullable();
            $table->tinyInteger('tipo')->default(1)->comment('1=Notificaciones, 2=Documentos, 3=Comunicados');
            $table->text('detalle_json')->nullable()->comment('JSON completo del detalle');
            $table->timestamps();

            $table->unique(['company_id', 'cod_sunat'], 'uq_buzon_mensajes_company_cod');
            $table->index(['company_id', 'tipo', 'fecha'], 'idx_buzon_mensajes_filter');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buzon_mensajes');
    }
};
