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
        Schema::table('invoices', function (Blueprint $table) {
            // Link to guía de remisión (MANDATORY for all invoices)
            if (!Schema::hasColumn('invoices', 'guia_remision_id')) {
                $table->foreignId('guia_remision_id')
                    ->nullable()
                    ->constrained('guia_remisions')
                    ->cascadeOnDelete();
            }

            // Store which address this invoice was sent to
            if (!Schema::hasColumn('invoices', 'client_address_id')) {
                $table->foreignId('client_address_id')
                    ->nullable()
                    ->constrained('client_addresses')
                    ->nullableOnDelete();
            }

            // Retention tracking (persistence for SUNAT + PDF)
            if (!Schema::hasColumn('invoices', 'has_retention')) {
                $table->boolean('has_retention')
                    ->default(false);
            }

            if (!Schema::hasColumn('invoices', 'retention_amount')) {
                $table->decimal('retention_amount', 12, 2)
                    ->default(0);
            }

            if (!Schema::hasColumn('invoices', 'retention_percentage')) {
                $table->decimal('retention_percentage', 5, 2)
                    ->default(3.00);
            }

            // Total before/after retention (for SUNAT XML + PDF + letters)
            if (!Schema::hasColumn('invoices', 'total_before_retention')) {
                $table->decimal('total_before_retention', 12, 2)
                    ->nullable();
            }

            if (!Schema::hasColumn('invoices', 'total_after_retention')) {
                $table->decimal('total_after_retention', 12, 2)
                    ->nullable();
            }

            // Indexes for performance
            if (!Schema::hasIndex('invoices', 'invoices_guia_remision_id_index')) {
                $table->index('guia_remision_id');
            }
            if (!Schema::hasIndex('invoices', 'invoices_company_id_index')) {
                $table->index(['company_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasIndex('invoices', 'invoices_company_id_index')) {
                $table->dropIndex('invoices_company_id_index');
            }
            if (Schema::hasIndex('invoices', 'invoices_guia_remision_id_index')) {
                $table->dropIndex('invoices_guia_remision_id_index');
            }
            if (Schema::hasColumn('invoices', 'guia_remision_id')) {
                $table->dropForeign(['guia_remision_id']);
                $table->dropColumn('guia_remision_id');
            }
            if (Schema::hasColumn('invoices', 'client_address_id')) {
                $table->dropForeign(['client_address_id']);
                $table->dropColumn('client_address_id');
            }
            if (Schema::hasColumn('invoices', 'has_retention')) {
                $table->dropColumn('has_retention');
            }
            if (Schema::hasColumn('invoices', 'retention_amount')) {
                $table->dropColumn('retention_amount');
            }
            if (Schema::hasColumn('invoices', 'retention_percentage')) {
                $table->dropColumn('retention_percentage');
            }
            if (Schema::hasColumn('invoices', 'total_before_retention')) {
                $table->dropColumn('total_before_retention');
            }
            if (Schema::hasColumn('invoices', 'total_after_retention')) {
                $table->dropColumn('total_after_retention');
            }
        });
    }
};
