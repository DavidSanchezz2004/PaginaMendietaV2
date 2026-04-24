<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

$db = $app->make('Illuminate\Database\ConnectionResolverInterface')->connection();

// Insertar una dirección para el cliente Leonardo (ID 2)
$result = $db->insert(
    "INSERT INTO client_addresses (client_id, type, street, city, state, postal_code, is_default, created_at, updated_at) 
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
    [
        2,                           // client_id (Leonardo)
        'delivery',                  // type
        'Av. Principal 123, Piso 5', // street
        'Lima',                      // city
        'Lima',                      // state
        '15001',                     // postal_code
        true,                        // is_default
        date('Y-m-d H:i:s'),        // created_at
        date('Y-m-d H:i:s'),        // updated_at
    ]
);

echo "Dirección creada para cliente Leonardo (ID 2)\n";
echo "INSERT result: " . ($result ? "SUCCESS" : "FAILED") . "\n";

// Verificar
$addresses = $db->select("SELECT * FROM client_addresses WHERE client_id = 2");
echo "Direcciones totales: " . count($addresses) . "\n";
foreach ($addresses as $addr) {
    echo "  - ID: {$addr->id}, Type: {$addr->type}, Street: {$addr->street}\n";
}
