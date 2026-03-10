<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obligation_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month'); // 1-12
            $table->foreignId('declared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('declared_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'period_year', 'period_month'], 'unique_declaration_per_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obligation_declarations');
    }
};
