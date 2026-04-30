<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            if (! Schema::hasColumn('invoices', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        if (! Schema::hasTable('invoice_send_logs')) {
            Schema::create('invoice_send_logs', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 30)->default('emit');
                $table->unsignedInteger('attempt_number')->default(1);
                $table->string('endpoint', 150)->nullable();
                $table->string('codigo_tipo_documento', 2)->nullable();
                $table->string('serie_documento', 5)->nullable();
                $table->string('numero_documento', 10)->nullable();
                $table->string('codigo_interno', 30)->nullable();
                $table->decimal('monto_total', 14, 2)->nullable();
                $table->boolean('success')->default(false);
                $table->unsignedSmallInteger('http_status')->nullable();
                $table->string('codigo_respuesta', 20)->nullable();
                $table->text('mensaje_respuesta')->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->timestamps();

                $table->index(['company_id', 'invoice_id', 'action'], 'invoice_send_logs_invoice_action_idx');
                $table->index(['company_id', 'codigo_tipo_documento', 'serie_documento', 'numero_documento'], 'invoice_send_logs_document_idx');
                $table->index(['company_id', 'created_at'], 'invoice_send_logs_company_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_send_logs');

        Schema::table('invoices', function (Blueprint $table): void {
            if (Schema::hasColumn('invoices', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
