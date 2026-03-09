<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('final_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('document_type'); // e.g. pdj, ficha_ruc
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->index('company_id');
            $table->index('document_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_documents');
    }
};
