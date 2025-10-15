<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Status Mappings
    |--------------------------------------------------------------------------
    |
    | This configuration defines the various payment status categories used
    | across different payment providers. These mappings help standardize
    | payment status handling regardless of the provider's specific statuses.
    |
    */

    'status_mappings' => [
        'success_statuses' => [
            'SUCCESS',
            'SUCCESSFUL', 
            'SUCCESSFULL',
            'COMPLETED',
            'PAID',
        ],
        'pending_statuses' => [
            'PENDING',
            'PROCESSING',
            'INITIATED',
            'WAITING_FOR_PIN',
            'AWAITING_CONFIRMATION',
        ],
        'failed_statuses' => [
            'FAILED',
            'CANCELLED',
            'EXPIRED',
            'DECLINED',
            'TIMEOUT',
        ],
        'terminal_statuses' => [
            'SUCCESS',
            'SUCCESSFULL',
            'COMPLETED',
            'PAID',
            'FAILED',
            'CANCELLED',
            'EXPIRED',
            'DECLINED',
            'TIMEOUT',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Status Codes
    |--------------------------------------------------------------------------
    |
    | HTTP-like status codes for payment transactions. These codes provide
    | a standardized way to represent payment states across different
    | payment providers and internal systems.
    |
    */

    'transaction_status_codes' => [
        'success' => '200',
        'pending' => ['100', '101', '102'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Provider Settings
    |--------------------------------------------------------------------------
    |
    | General payment provider configurations that apply across
    | multiple providers in the system.
    |
    */

    'providers' => [
        'mtn' => [
            'name' => 'MTN Mobile Money',
            'currency' => 'XAF',
            'country' => 'CM',
        ],
        'orange' => [
            'name' => 'Orange Money',
            'currency' => 'XAF',
            'country' => 'CM',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | Default values used when provider-specific configurations
    | are not available or specified.
    |
    */

    'defaults' => [
        'currency' => 'XAF',
        'timeout' => 30,
        'retry_attempts' => 3,
    ],

];
