<?php
// Verificar migraciones ejecutadas

use Illuminate\Support\Facades\Schema;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TABLAS CREADAS ===\n";
echo 'quotes: ' . (Schema::hasTable('quotes') ? '✅' : '❌') . "\n";
echo 'quote_items: ' . (Schema::hasTable('quote_items') ? '✅' : '❌') . "\n";
echo 'company_settings: ' . (Schema::hasTable('company_settings') ? '✅' : '❌') . "\n";

echo "\n=== CAMPOS EN INVOICES ===\n";
$invoiceColumns = Schema::getColumnListing('invoices');
$invoiceFields = ['retention_enabled', 'retention_base', 'retention_percentage', 'retention_amount', 'net_total', 'retention_info'];
foreach ($invoiceFields as $field) {
    echo "$field: " . (in_array($field, $invoiceColumns) ? '✅' : '❌') . "\n";
}

echo "\n=== CAMPOS EN PURCHASES ===\n";
$purchaseColumns = Schema::getColumnListing('purchases');
$purchaseFields = ['retention_enabled', 'retention_base', 'retention_percentage', 'retention_amount', 'net_total', 'retention_info'];
foreach ($purchaseFields as $field) {
    echo "$field: " . (in_array($field, $purchaseColumns) ? '✅' : '❌') . "\n";
}

echo "\n=== CAMPOS EN LETRAS_CAMBIO ===\n";
$letrasColumns = Schema::getColumnListing('letras_cambio');
echo 'invoice_id: ' . (in_array('invoice_id', $letrasColumns) ? '✅' : '❌') . "\n";
echo 'purchase_id: ' . (in_array('purchase_id', $letrasColumns) ? '✅' : '❌') . "\n";

echo "\n=== CONTEO DE REGISTROS ===\n";
echo 'Quotes: ' . \DB::table('quotes')->count() . "\n";
echo 'Quote Items: ' . \DB::table('quote_items')->count() . "\n";
echo 'Company Settings: ' . \DB::table('company_settings')->count() . "\n";
