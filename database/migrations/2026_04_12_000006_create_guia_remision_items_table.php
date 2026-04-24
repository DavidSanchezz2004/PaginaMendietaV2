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
        Schema::create('guia_remision_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('guia_remision_id')
                ->constrained('guia_remisions')
                ->cascadeOnDelete();

            $table->foreignId('purchase_item_id')
                ->constrained('purchase_items')
                ->cascadeOnDelete();

            // Quantity shipped in this guide
            $table->decimal('quantity', 12, 4);
            $table->string('unit');
            $table->string('description');

            $table->timestamps();

            $table->index('guia_remision_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guia_remision_items');
    }
};
