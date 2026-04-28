<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('letter_exchange_status', 30)->nullable()->after('total_after_retention');
            $table->timestamp('letter_exchanged_at')->nullable()->after('letter_exchange_status');
            $table->text('letter_exchange_observation')->nullable()->after('letter_exchanged_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'letter_exchange_status',
                'letter_exchanged_at',
                'letter_exchange_observation',
            ]);
        });
    }
};
