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
        Schema::table('purchase_items', function (Blueprint $table) {
            // Track how much of each item has been invoiced (partial invoicing)
            if (!Schema::hasColumn('purchase_items', 'invoiced_quantity')) {
                $table->decimal('invoiced_quantity', 12, 4)
                    ->default(0);

                // Index for queries checking invoiced vs total quantity
                if (!Schema::hasIndex('purchase_items', 'purchase_items_purchase_id_index')) {
                    $table->index(['purchase_id']);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            if (Schema::hasIndex('purchase_items', 'purchase_items_purchase_id_index')) {
                $table->dropIndex('purchase_items_purchase_id_index');
            }
            if (Schema::hasColumn('purchase_items', 'invoiced_quantity')) {
                $table->dropColumn('invoiced_quantity');
            }
        });
    }
};
