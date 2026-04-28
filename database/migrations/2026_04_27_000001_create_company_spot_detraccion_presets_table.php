<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_spot_detraccion_presets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('spot_detraccion_id')->nullable()->constrained('spot_detracciones')->nullOnDelete();
            $table->string('name', 80);
            $table->string('codigo_bbss_sujeto_detraccion', 5);
            $table->decimal('porcentaje_detraccion', 8, 2);
            $table->string('cuenta_banco_detraccion', 20)->nullable();
            $table->string('codigo_medio_pago_detraccion', 5)->default('001');
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'is_default']);
            $table->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_spot_detraccion_presets');
    }
};
