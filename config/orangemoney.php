<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Orange Money API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Orange Money payment gateway integration.
    | Make sure to set these values in your .env file.
    |
    */

    // API Base URL and Endpoints
    'base_url' => env('OM_BASE_URL', 'https://api-s1.orange.cm'),

    'endpoints' => [
        'token' => env('OM_TOKEN_ENDPOINT', '/token'),
        'init' => env('OM_INIT_ENDPOINT', '/omcoreapis/1.0.2/mp/init'),
        'pay' => env('OM_PAY_ENDPOINT', '/omcoreapis/1.0.2/mp/pay'),
        'status' => env('OM_STATUS_ENDPOINT', '/omcoreapis/1.0.2/mp/paymentstatus/'),
    ],

    // Real Orange Money API credentials
    'client_id' => env('OM_CLIENT_ID', 'FnzX3Du_l7jQBFgqDKRlhrFfe7Aa'),
    'client_secret' => env('OM_CLIENT_SECRET', 'Q58xpZiMmZSAfIL8Hc1d0nnzcfYa'),
    'api_username' => env('OM_API_USERNAME', 'OMSANDBOXAPI'),
    'api_password' => env('OM_API_PASSWORD', 'OMS@NDBOX@PI'),

    // Legacy package credentials (for backward compatibility)
    'auth_header' => env('OM_AUTH_HEADER', '8c0fe2eb-1591-3b77-a099-c5bd0ca163a6'),
    'merchant_key' => env('OM_MERCHANT_KEY', 'FnzX3Du_l7jQBFgqDKRlhrFfe7Aa'),

    'channel_user_msisdn' => env('OM_CHANNEL_USER_MSISDN', '691301143'),
    'pin' => env('OM_PIN', '2222'),
    'description' => env('OM_DESCRIPTION', 'Paiement Petrolex'),

    // Callback URLs
    'return_url' => env('OM_RETURN_URL', env('APP_URL').'/callback/cm/orange'),
    'cancel_url' => env('OM_CANCEL_URL', env('APP_URL').'/callback/cm/orange'),
    'notif_url' => env('OM_NOTIF_URL', env('APP_URL').'/callback/cm/orange'),
];
