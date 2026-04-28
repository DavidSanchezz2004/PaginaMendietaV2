<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FeasaPeru API
    |--------------------------------------------------------------------------
    | Base URL de la API de FeasaPeru para comunicación con SUNAT.
    | El token es POR EMPRESA, almacenado encriptado en companies.feasy_token.
    | NO existe token global aquí.
    */
    'feasy_base_url' => env('FEASY_BASE_URL', 'https://api.feasyperu.com/api'),

    /*
    |--------------------------------------------------------------------------
    | Timeout de requests a Feasy (segundos)
    |--------------------------------------------------------------------------
    */
    'feasy_timeout' => (int) env('FEASY_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Storage privado para XML
    |--------------------------------------------------------------------------
    | Los XML de comprobantes se guardan fuera del acceso público.
    | Path relativo al disco 'local' (storage/app/):
    |   private/companies/{company_id}/xml/{nombre_archivo.xml}
    */
    'xml_storage_disk' => 'local',
    'xml_storage_path' => 'private/companies',

    /*
    |--------------------------------------------------------------------------
    | IGV por defecto (Perú)
    |--------------------------------------------------------------------------
    */
    'igv_porcentaje' => (float) env('SUNAT_IGV', 18.00),

    /*
    |--------------------------------------------------------------------------
    | Límite mensual de comprobantes emitidos
    |--------------------------------------------------------------------------
    | Plan contratado del facturador. Se consume cuando un comprobante queda
    | emitido/aceptado y se controla de forma global para el estudio.
    */
    'monthly_document_limit' => (int) env('FACTURADOR_MONTHLY_DOCUMENT_LIMIT', 500),

];
