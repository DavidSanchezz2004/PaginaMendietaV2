<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubigeos', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 6)->unique();
            $table->string('department', 100);
            $table->string('province', 100);
            $table->string('district', 120);
            $table->string('legal_capital', 120)->nullable();
            $table->timestamps();

            $table->index(['department', 'province']);
            $table->index('district');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubigeos');
    }
};
