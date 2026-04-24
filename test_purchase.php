<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$db = $app->make('db');

$purchase = $db->table('purchases')->where('id', 1)->first();
echo "Purchase ID: 1\n";
echo "Status: " . ($purchase?->status ?? 'NULL') . "\n";
echo "Client ID: " . ($purchase?->client_id ?? 'NULL') . "\n";

if ($purchase?->client_id) {
    $addresses = $db->table('client_addresses')->where('client_id', $purchase->client_id)->count();
    echo "Addresses count: " . $addresses . "\n";
}
