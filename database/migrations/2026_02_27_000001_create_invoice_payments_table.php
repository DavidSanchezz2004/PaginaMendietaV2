<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('metodo', 50)->comment('efectivo, yape, plin, transferencia, deposito, tarjeta, otro');
            $table->decimal('monto', 12, 2)->default(0);
            $table->string('referencia', 150)->nullable()->comment('N° operación, código de transacción, etc.');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
