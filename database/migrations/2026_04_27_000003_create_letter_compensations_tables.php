<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_compensations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bill_of_exchange_id')->constrained('letras_cambio')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('providers')->restrictOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('compensation_date');
            $table->string('currency', 3);
            $table->decimal('total_amount', 15, 2);
            $table->text('observation')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'supplier_id']);
            $table->index(['bill_of_exchange_id', 'compensation_date']);
        });

        Schema::create('letter_compensation_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('letter_compensation_id')->constrained('letter_compensations')->cascadeOnDelete();
            $table->foreignId('purchase_invoice_id')->constrained('purchases')->restrictOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->index('purchase_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_compensation_details');
        Schema::dropIfExists('letter_compensations');
    }
};
