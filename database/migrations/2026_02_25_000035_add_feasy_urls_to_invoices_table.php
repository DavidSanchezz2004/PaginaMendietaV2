<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega los campos de trazabilidad que devuelve Feasy en la consulta:
 *  - ruta_xml       → URL externa para descargar el XML
 *  - ruta_cdr       → URL externa para descargar el CDR (constancia SUNAT)
 *  - ruta_reporte   → URL externa para descargar el reporte PDF
 *  - hash_cpe       → Hash CPE generado por SUNAT (codigo_hash en la API)
 *  - valor_qr       → Valor para generar el código QR
 *  - mensaje_observacion → Observación devuelta por SUNAT
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('ruta_xml', 500)->nullable()
                ->comment('URL Feasy para descargar el XML del comprobante')
                ->after('xml_path');

            $table->string('ruta_cdr', 500)->nullable()
                ->comment('URL Feasy para descargar el CDR (constancia de recepción SUNAT)')
                ->after('ruta_xml');

            $table->string('ruta_reporte', 500)->nullable()
                ->comment('URL Feasy para descargar el reporte PDF')
                ->after('ruta_cdr');

            $table->string('hash_cpe', 200)->nullable()
                ->comment('Código HASH del comprobante (codigo_hash en Feasy)')
                ->after('ruta_reporte');

            $table->text('valor_qr')->nullable()
                ->comment('Valor para generar el código QR del comprobante')
                ->after('hash_cpe');

            $table->text('mensaje_observacion')->nullable()
                ->comment('Mensaje de observación devuelto por SUNAT')
                ->after('valor_qr');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropColumn([
                'ruta_xml',
                'ruta_cdr',
                'ruta_reporte',
                'hash_cpe',
                'valor_qr',
                'mensaje_observacion',
            ]);
        });
    }
};
