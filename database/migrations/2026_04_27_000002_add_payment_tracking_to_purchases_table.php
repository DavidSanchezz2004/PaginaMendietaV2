<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            if (! Schema::hasColumn('purchases', 'monto_pagado')) {
                $table->decimal('monto_pagado', 15, 2)->default(0)->after('monto_total');
            }

            if (! Schema::hasColumn('purchases', 'estado_pago')) {
                $table->string('estado_pago', 30)->default('pendiente')->after('monto_pagado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table): void {
            if (Schema::hasColumn('purchases', 'estado_pago')) {
                $table->dropColumn('estado_pago');
            }

            if (Schema::hasColumn('purchases', 'monto_pagado')) {
                $table->dropColumn('monto_pagado');
            }
        });
    }
};
