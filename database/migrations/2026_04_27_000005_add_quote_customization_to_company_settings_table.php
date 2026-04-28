<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('company_settings', 'quote_enabled')) {
                $table->boolean('quote_enabled')->default(true)->after('company_id');
            }

            if (! Schema::hasColumn('company_settings', 'quote_logo_base64')) {
                $table->longText('quote_logo_base64')->nullable()->after('logo_path');
            }

            if (! Schema::hasColumn('company_settings', 'quote_payment_info')) {
                $table->json('quote_payment_info')->nullable()->after('bank_accounts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('company_settings', 'quote_payment_info')) {
                $table->dropColumn('quote_payment_info');
            }

            if (Schema::hasColumn('company_settings', 'quote_logo_base64')) {
                $table->dropColumn('quote_logo_base64');
            }

            if (Schema::hasColumn('company_settings', 'quote_enabled')) {
                $table->dropColumn('quote_enabled');
            }
        });
    }
};
