<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('sunat_token')->nullable()->after('clave_sol');
            $table->timestamp('sunat_token_expires_at')->nullable()->after('sunat_token');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['sunat_token', 'sunat_token_expires_at']);
        });
    }
};
