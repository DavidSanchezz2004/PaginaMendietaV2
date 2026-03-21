<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buzon_keywords', function (Blueprint $table): void {
            $table->id();
            $table->string('palabra');
            $table->enum('prioridad', ['alta', 'media', 'baja'])->default('media');
            $table->string('color', 7)->default('#3b82f6')->comment('Hex color para badge');
            $table->timestamps();

            $table->unique('palabra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buzon_keywords');
    }
};
