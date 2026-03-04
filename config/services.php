<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'aqpfact' => [
        'base_url' => env('AQPFACT_BASE_URL', 'https://apis.aqpfact.pe/api'),
        'token' => env('AQPFACT_TOKEN'),
        'verify_ssl' => env('AQPFACT_VERIFY_SSL', true),
    ],

    // Token global del portal Feasy (1 cuenta → 1 token → muchas empresas por RUC)
    'feasy' => [
        'token' => env('FEASY_TOKEN', ''),
    ],

    'recaptcha' => [
        'site_key'   => env('RECAPTCHA_SITE_KEY', ''),
        'secret_key' => env('RECAPTCHA_SECRET_KEY', ''),
        // Puntuación mínima aceptada (0.0–1.0). Por debajo se rechaza el login.
        'threshold'  => env('RECAPTCHA_THRESHOLD', 0.5),
    ],

    // Microservicio bot_cookies: autenticación automática en portales externos (SUNAT, etc.)
    'bot_cookies' => [
        'url' => env('BOT_COOKIES_URL', 'http://localhost:8001'),
        'key' => env('BOT_COOKIES_KEY'),
    ],

];