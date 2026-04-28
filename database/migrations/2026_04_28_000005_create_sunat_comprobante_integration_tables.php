<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sunat_api_credentials')) {
            Schema::create('sunat_api_credentials', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('empresa_id')->constrained('companies')->cascadeOnDelete();
                $table->string('ruc_consultante', 11);
                $table->string('client_id');
                $table->text('client_secret');
                $table->string('scope')->default('https://api.sunat.gob.pe/v1/contribuyente/contribuyentes');
                $table->string('token_url')->default('https://api-seguridad.sunat.gob.pe/v1/clientesextranet/{client_id}/oauth2/token/');
                $table->string('consulta_url')->default('https://api.sunat.gob.pe/v1/contribuyente/contribuyentes/{ruc}/validarcomprobante');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_token_generated_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();

                $table->unique('empresa_id');
                $table->index(['empresa_id', 'is_active'], 'sunat_cred_empresa_active_idx');
            });
        }

        if (! Schema::hasTable('sunat_tokens')) {
            Schema::create('sunat_tokens', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('empresa_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('sunat_api_credential_id')->constrained('sunat_api_credentials')->cascadeOnDelete();
                $table->text('access_token');
                $table->string('token_type')->default('Bearer');
                $table->unsignedInteger('expires_in')->default(3600);
                $table->timestamp('expires_at');
                $table->timestamp('generated_at');
                $table->timestamps();

                $table->index(['empresa_id', 'sunat_api_credential_id', 'expires_at'], 'sunat_tokens_empresa_cred_exp_idx');
            });
        }

        if (! Schema::hasTable('sunat_comprobante_validaciones')) {
            Schema::create('sunat_comprobante_validaciones', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('empresa_id')->constrained('companies')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sunat_api_credential_id')->nullable()->constrained('sunat_api_credentials')->nullOnDelete();
                $table->string('ruc_consultante', 11);
                $table->string('num_ruc_emisor', 11);
                $table->string('cod_comp', 2);
                $table->string('numero_serie', 4);
                $table->unsignedInteger('numero');
                $table->date('fecha_emision');
                $table->decimal('monto', 10, 2)->nullable();
                $table->boolean('success')->default(false);
                $table->string('message')->nullable();
                $table->string('estado_cp', 2)->nullable();
                $table->string('estado_cp_texto')->nullable();
                $table->string('estado_ruc', 2)->nullable();
                $table->string('estado_ruc_texto')->nullable();
                $table->string('cond_domi_ruc', 2)->nullable();
                $table->string('cond_domi_ruc_texto')->nullable();
                $table->json('observaciones')->nullable();
                $table->string('error_code')->nullable();
                $table->json('request_payload');
                $table->json('response_payload')->nullable();
                $table->timestamps();

                $table->index(['empresa_id', 'created_at'], 'sunat_val_empresa_fecha_idx');
                $table->index(['empresa_id', 'num_ruc_emisor'], 'sunat_val_empresa_emisor_idx');
                $table->index(['empresa_id', 'cod_comp', 'estado_cp'], 'sunat_val_empresa_tipo_estado_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sunat_comprobante_validaciones');
        Schema::dropIfExists('sunat_tokens');
        Schema::dropIfExists('sunat_api_credentials');
    }
};
