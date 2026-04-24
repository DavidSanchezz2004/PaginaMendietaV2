<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('guia_remisions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('purchase_id')
                ->constrained('purchases')
                ->cascadeOnDelete();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->foreignId('client_address_id')
                ->constrained('client_addresses')
                ->cascadeOnDelete();

            $table->string('numero')->unique();
            $table->date('fecha_emision');
            $table->string('motivo')->default('Venta');

            $table->enum('estado', ['draft', 'generated', 'invoiced'])
                ->default('draft');

            // Link to invoice after it's created
            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->nullableOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index(['company_id', 'estado']);
            $table->index(['purchase_id', 'estado']);
            $table->index('client_address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guia_remisions');
    }
};
