<?php
// Crear CompanySetting de prueba

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CompanySetting;
use Illuminate\Support\Facades\DB;

// Obtener primer empresa
$company = DB::table('companies')->first();

if (!$company) {
    echo "❌ No hay empresas en la BD. Crea una primero.\n";
    exit(1);
}

echo "Creando CompanySetting para empresa: {$company->id}\n";

$setting = CompanySetting::firstOrCreate(
    ['company_id' => $company->id],
    [
        'company_name' => 'EMPRESA MENDIETA SA',
        'ruc' => '20123456789',
        'address' => 'Calle Principal 123, Lima, Perú',
        'primary_color' => '#1a6b57',
        'secondary_color' => '#e5f5f1',
        'logo_path' => null,
        'bank_accounts' => json_encode([
            [
                'banco' => 'BCP',
                'cuenta' => '191-2345678-9-10',
                'cci' => '002001234567890123456'
            ],
            [
                'banco' => 'Interbank',
                'cuenta' => '123-456789-01',
                'cci' => '003700123456789012345'
            ]
        ]),
        'quote_footer' => 'Esta cotización es válida por 30 días',
        'quote_terms' => 'Términos y condiciones de la empresa',
        'quote_thanks_message' => '¡Gracias por su confianza!'
    ]
);

echo "✅ CompanySetting creado/actualizado:\n";
echo "   ID: {$setting->id}\n";
echo "   Empresa: {$setting->company_name}\n";
echo "   RUC: {$setting->ruc}\n";
echo "   Color Primario: {$setting->primary_color}\n";
