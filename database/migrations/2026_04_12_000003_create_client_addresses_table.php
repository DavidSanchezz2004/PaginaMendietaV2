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
        Schema::create('client_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            $table->enum('type', ['fiscal', 'delivery'])
                ->default('delivery');

            $table->string('street');
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();

            // PRO: Default address for UX (auto-select in forms)
            $table->boolean('is_default')
                ->default(false);

            $table->timestamps();

            // Indexes for common queries
            $table->index(['client_id', 'type']);
            $table->index(['client_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_addresses');
    }
};
