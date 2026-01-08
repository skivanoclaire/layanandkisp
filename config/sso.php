<?php

return [
    'public_key' => env('SSO_PUBLIC_KEY'),
    'issuer' => env('SSO_ISSUER'),
    'audience' => env('SSO_AUDIENCE'),
    'exchange_url' => env('SSO_EXCHANGE_URL'),
    'api_key' => env('SSO_API_KEY'),
    'exchange_timeout' => (int) env('SSO_EXCHANGE_TIMEOUT', 10),
    'leeway' => (int) env('SSO_JWT_LEEWAY', 60),
    'allowed_realms' => array_filter(array_map('trim', explode(',', env('SSO_ALLOWED_REALMS', 'asn-kaltara,public-realm')))),
    'role_map' => [
        'admin' => 'Admin',
        'operator-opd' => 'Operator-OPD',
        'operator-vidcon' => 'Operator-Vidcon',
        'operator-sandi' => 'Operator-Sandi',
        'public' => 'user',
    ],
];
