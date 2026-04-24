<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buzon_lecturas', function (Blueprint $table): void {
            $table->foreignId('buzon_mensaje_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('leido_at')->useCurrent();

            $table->primary(['buzon_mensaje_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buzon_lecturas');
    }
};
