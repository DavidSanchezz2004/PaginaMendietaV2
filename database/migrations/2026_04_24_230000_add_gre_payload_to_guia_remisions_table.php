<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guia_remisions', function (Blueprint $table): void {
            if (! Schema::hasColumn('guia_remisions', 'gre_payload')) {
                $table->json('gre_payload')
                    ->nullable()
                    ->after('motivo')
                    ->comment('Payload estructurado GRE Remitente compatible con Feasy.');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guia_remisions', function (Blueprint $table): void {
            if (Schema::hasColumn('guia_remisions', 'gre_payload')) {
                $table->dropColumn('gre_payload');
            }
        });
    }
};
