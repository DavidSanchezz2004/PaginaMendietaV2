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
        Schema::table('clients', function (Blueprint $table) {
            // Add is_retainer_agent flag (3% retention applies if true)
            if (!Schema::hasColumn('clients', 'is_retainer_agent')) {
                $table->boolean('is_retainer_agent')
                    ->default(false);

                $table->index('is_retainer_agent');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            if (Schema::hasIndex('clients', 'clients_is_retainer_agent_index')) {
                $table->dropIndex('clients_is_retainer_agent_index');
            }
            if (Schema::hasColumn('clients', 'is_retainer_agent')) {
                $table->dropColumn('is_retainer_agent');
            }
        });
    }
};
