<?php

return [
    'scope' => env('SUNAT_API_SCOPE', 'https://api.sunat.gob.pe/v1/contribuyente/contribuyentes'),
    'token_url' => env('SUNAT_TOKEN_URL', 'https://api-seguridad.sunat.gob.pe/v1/clientesextranet/{client_id}/oauth2/token/'),
    'consulta_url' => env('SUNAT_CONSULTA_CP_URL', 'https://api.sunat.gob.pe/v1/contribuyente/contribuyentes/{ruc}/validarcomprobante'),
    'timeout' => (int) env('SUNAT_API_TIMEOUT', 25),
];
