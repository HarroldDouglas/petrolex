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

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'google' => [
        'maps' => [
            'api_key' => env('GOOGLE_MAPS_API_KEY'),
        ],
    ],

    // Payment Gateway External Server Configuration
    'payment_gateways' => [
        'external_server' => [
            'base_url' => env('PAYMENT_EXTERNAL_SERVER_BASE', env('APP_URL')),
            'timeout' => env('PAYMENT_EXTERNAL_SERVER_TIMEOUT', 30),
            'mtn_endpoint' => env('PAYMENT_EXTERNAL_MTN_ENDPOINT', '/callback/cm/momo'),
            'orange_endpoint' => env('PAYMENT_EXTERNAL_ORANGE_ENDPOINT', '/callback/cm/orange'),
        ],
    ],
];
