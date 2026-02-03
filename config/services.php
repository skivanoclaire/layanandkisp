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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'captcha' => [
    'sitekey' => env('NOCAPTCHA_SITEKEY'),
    'secret' => env('NOCAPTCHA_SECRET'),
    ],

    'simpeg' => [
        'api_url' => env('SIMPEG_API_URL', 'https://api-splp.layanan.go.id:443/api_simpeg_pegawai/v1'),
        'api_key' => env('SIMPEG_API_KEY'),
    ],

    'cloudflare' => [
        'api_token' => env('CLOUDFLARE_API_TOKEN'),
        'analytics_token' => env('CLOUDFLARE_ANALYTICS_TOKEN'),
        'zone_id' => env('CLOUDFLARE_ZONE_ID'),
        'zone_name' => env('CLOUDFLARE_ZONE_NAME', 'kaltaraprov.go.id'),
    ],

    'whm' => [
        'host' => env('WHM_HOST', 'mail.kaltaraprov.go.id'),
        'username' => env('WHM_USERNAME', 'root'),
        'token' => env('WHM_TOKEN'),
    ],

    'keycloak' => [
        'client_id' => env('KEYCLOAK_CLIENT_ID'),
        'client_secret' => env('KEYCLOAK_CLIENT_SECRET'),
        'redirect' => env('KEYCLOAK_REDIRECT_URI'),
        'base_url' => env('KEYCLOAK_BASE_URL', 'https://sso.kaltaraprov.go.id'),
        'realms' => env('KEYCLOAK_REALM', 'asn-kaltara'),  // Note: 'realms' with 's'
    ],

];
