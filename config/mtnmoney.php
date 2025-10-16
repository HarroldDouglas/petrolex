<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MTN MoMo API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for MTN Mobile Money payment gateway integration.
    | Based on MTN MoMo API v1.0 specification.
    |
    */

    'base_url' => env('MTN_MOMO_BASE_URL', 'https://proxy.momoapi.mtn.com'),

    'endpoints' => [
        'collection' => [
            'token' => '/collection/token/',
            'request_to_pay' => '/collection/v1_0/requesttopay',
            'transaction_status' => '/collection/v1_0/requesttopay/{referenceId}',
        ],
        'disbursement' => [
            'token' => '/disbursement/token/',
            'transfer' => '/disbursement/v1_0/transfer',
        ],
        'remittance' => [
            'token' => '/remittance/token/',
            'transfer' => '/remittance/v1_0/transfer',
        ],
    ],

    'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
    'api_user' => env('MTN_MOMO_API_USER'),
    'api_key' => env('MTN_MOMO_API_KEY'),

    'target_environment' => env('MTN_MOMO_TARGET_ENVIRONMENT', 'mtncameroon'),
    'currency' => env('MTN_MOMO_CURRENCY', 'XAF'),

    'callback_url' => env('MTN_MOMO_CALLBACK_URL', env('APP_URL').'/api/callback/cm/momo'),

    'timeout' => env('MTN_MOMO_TIMEOUT', 30),

    'default_payer_message' => env('MTN_MOMO_PAYER_MESSAGE', 'Paiement Petrolex'),
    'default_payee_note' => env('MTN_MOMO_PAYEE_NOTE', 'Transaction Petrolex'),

    'test_numbers' => [
        'success' => ['237677000001', '237677000010'],
        'pending' => ['237677000002', '237677000020'],
        'failure' => ['237677000003', '237677000030'],
        'timeout' => ['237677000004', '237677000040'],
        'invalid' => ['237677000005', '237677000050'],
    ],

    'party_id_type' => 'MSISDN',

];
