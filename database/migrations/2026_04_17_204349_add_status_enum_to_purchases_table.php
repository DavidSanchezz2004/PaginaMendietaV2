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
        Schema::table('purchases', function (Blueprint $table) {
            // Add status enum for workflow tracking (registered → assigned → guided → partially_invoiced → invoiced)
            if (!Schema::hasColumn('purchases', 'status')) {
                $table->enum('status', ['registered', 'assigned', 'guided', 'partially_invoiced', 'invoiced'])
                    ->default('registered')
                    ->after('client_id');
                
                $table->index('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasIndex('purchases', 'purchases_status_index')) {
                $table->dropIndex('purchases_status_index');
            }
            if (Schema::hasColumn('purchases', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
