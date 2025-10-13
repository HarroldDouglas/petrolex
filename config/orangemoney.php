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

    'base_url' => env('OM_BASE_URL', 'https://api-s1.orange.cm'),

    'endpoints' => [
        'token' => env('OM_TOKEN_ENDPOINT', '/token'),
        'init' => env('OM_INIT_ENDPOINT', '/omcoreapis/1.0.2/mp/init'),
        'pay' => env('OM_PAY_ENDPOINT', '/omcoreapis/1.0.2/mp/pay'),
        'status' => env('OM_STATUS_ENDPOINT', '/omcoreapis/1.0.2/mp/paymentstatus/'),
    ],

    'client_id' => env('OM_CLIENT_ID'),
    'client_secret' => env('OM_CLIENT_SECRET'),
    'api_username' => env('OM_API_USERNAME'),
    'api_password' => env('OM_API_PASSWORD'),

    'auth_header' => env('OM_AUTH_HEADER'),
    'merchant_key' => env('OM_MERCHANT_KEY'),

    'channel_user_msisdn' => env('OM_CHANNEL_USER_MSISDN'),
    'pin' => env('OM_PIN'),
    'description' => env('OM_DESCRIPTION', 'Paiement Petrolex'),

    'return_url' => env('OM_RETURN_URL', env('APP_URL').'/callback/cm/orange'),
    'cancel_url' => env('OM_CANCEL_URL', env('APP_URL').'/callback/cm/orange'),
    'notif_url' => env('OM_NOTIF_URL', env('APP_URL').'/callback/cm/orange'),
];
