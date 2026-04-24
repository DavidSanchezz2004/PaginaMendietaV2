<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de configuración por empresa para cotizaciones.
 * Almacena datos dinámicos: logo, colores, bancos, etc.
 * 
 * Permite que cada cotización se vea con branding de la empresa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('company_id')
                ->unique()
                ->constrained('companies')
                ->cascadeOnDelete();

            // Branding
            $table->string('logo_path', 500)->nullable()
                ->comment('Ruta de logo: storage/companies/{id}/logo.png');
            $table->string('primary_color', 7)->default('#000000')
                ->comment('Color primario hex: #RRGGBB');
            $table->string('secondary_color', 7)->default('#CCCCCC')
                ->comment('Color secundario hex: #RRGGBB');

            // Información de la empresa (usualmente ya existe, pero redundancia para rapidez)
            $table->string('company_name', 200)->nullable();
            $table->string('ruc', 20)->nullable();
            $table->string('address', 300)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 200)->nullable();
            $table->text('website')->nullable();

            // Cuentas bancarias (JSON array)
            $table->json('bank_accounts')
                ->nullable()
                ->comment('JSON: [{banco: "BCP", cuenta: "12345", cci: "00123...", moneda: "PEN"}, ...]');

            // Textos personalizables
            $table->text('quote_footer')->nullable()
                ->comment('Pie de página en cotización');
            $table->text('quote_terms')->nullable()
                ->comment('Términos y condiciones de cotización');
            $table->text('quote_thanks_message')->nullable()
                ->comment('Mensaje de agradecimiento en PDF');

            // Configuración de cotización
            $table->boolean('show_igv_breakdown')->default(true)
                ->comment('¿Mostrar desglose de IGV?');
            $table->boolean('show_bank_accounts')->default(true)
                ->comment('¿Mostrar cuentas bancarias?');
            $table->boolean('require_client_email')->default(false)
                ->comment('¿Requerir email del cliente?');

            // PDF settings
            $table->string('paper_size', 10)->default('A4')
                ->comment('A4, Letter, etc.');
            $table->string('paper_orientation', 10)->default('portrait')
                ->comment('portrait, landscape');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
