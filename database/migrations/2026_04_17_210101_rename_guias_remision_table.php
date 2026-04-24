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
        if (Schema::hasTable('guias_remision')) {
            Schema::rename('guias_remision', 'guia_remisions');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('guia_remisions')) {
            Schema::rename('guia_remisions', 'guias_remision');
        }
    }
};
