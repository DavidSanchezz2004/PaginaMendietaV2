<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->boolean('is_good_taxpayer')->default(false)->after('facturador_enabled');
        });

        Schema::table('obligation_declarations', function (Blueprint $table): void {
            $table->string('due_group', 40)->nullable()->after('period_month');
            $table->date('due_date')->nullable()->after('due_group');
            $table->date('presentation_date')->nullable()->after('due_date');
            $table->string('status', 40)->default('pending')->after('presentation_date');
            $table->text('observation')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('obligation_declarations', function (Blueprint $table): void {
            $table->dropColumn([
                'due_group',
                'due_date',
                'presentation_date',
                'status',
                'observation',
            ]);
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn('is_good_taxpayer');
        });
    }
};
