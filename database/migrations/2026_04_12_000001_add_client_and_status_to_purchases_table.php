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
            // Add client_id for assignment flow
            if (!Schema::hasColumn('purchases', 'client_id')) {
                $table->foreignId('client_id')
                    ->nullable()
                    ->after('company_id')
                    ->constrained('clients')
                    ->nullableOnDelete();
            }

            // Add indexes for common queries
            if (!Schema::hasIndex('purchases', 'purchases_company_id_index')) {
                $table->index(['company_id']);
            }
            if (!Schema::hasIndex('purchases', 'purchases_client_id_index')) {
                $table->index(['client_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasIndex('purchases', 'purchases_company_id_index')) {
                $table->dropIndex('purchases_company_id_index');
            }
            if (Schema::hasIndex('purchases', 'purchases_client_id_index')) {
                $table->dropIndex('purchases_client_id_index');
            }
            if (Schema::hasColumn('purchases', 'client_id')) {
                $table->dropForeign(['client_id']);
                $table->dropColumn('client_id');
            }
        });
    }
};
