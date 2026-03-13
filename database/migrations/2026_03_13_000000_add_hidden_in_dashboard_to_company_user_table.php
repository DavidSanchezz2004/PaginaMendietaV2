<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('company_user', function (Blueprint $table): void {
            $table->boolean('hidden_in_dashboard')
                ->default(false)
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('company_user', function (Blueprint $table): void {
            $table->dropColumn('hidden_in_dashboard');
        });
    }
};

