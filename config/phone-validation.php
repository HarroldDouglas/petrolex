<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phone Validation Rules by Country
    |--------------------------------------------------------------------------
    |
    | Define phone number validation rules for each country using ISO 2-letter codes.
    | Each country can have: min_length, max_length, regex, and custom rules.
    |
    */

    'rules' => [
        // Cameroon
        'CM' => [
            'min_length' => 9,
            'max_length' => 9,
            'regex' => '/^6[0-9]{8}$/',
            'description' => 'Doit comporter 9 chiffres commençant par 6 (ex. : 677123456)',
        ],

        // France
        'FR' => [
            'min_length' => 10,
            'max_length' => 10,
            'regex' => '/^[0-9]{10}$/',
            'description' => 'Doit comporter 10 chiffres (ex. : 0123456789)',
        ],

        // United States
        'US' => [
            'min_length' => 10,
            'max_length' => 10,
            'regex' => '/^[0-9]{10}$/',
            'description' => 'Doit comporter 10 chiffres (ex. : 5551234567)',
        ],

        // United Kingdom
        'GB' => [
            'min_length' => 10,
            'max_length' => 11,
            'regex' => '/^[0-9]{10,11}$/',
            'description' => 'Doit comporter 10 à 11 chiffres',
        ],

        // Germany
        'DE' => [
            'min_length' => 10,
            'max_length' => 12,
            'regex' => '/^[0-9]{10,12}$/',
            'description' => 'Doit comporter 10 à 12 chiffres',
        ],

        // Nigeria
        'NG' => [
            'min_length' => 10,
            'max_length' => 10,
            'regex' => '/^[0-9]{10}$/',
            'description' => 'Doit comporter 10 chiffres',
        ],

        // Canada
        'CA' => [
            'min_length' => 10,
            'max_length' => 10,
            'regex' => '/^[0-9]{10}$/',
            'description' => 'Doit comporter 10 chiffres (ex. : 5551234567)',
        ],

        // Default rules for unknown countries
        'default' => [
            'min_length' => 7,
            'max_length' => 15,
            'regex' => '/^[0-9]{7,15}$/',
            'description' => 'Doit comporter entre 7 et 15 chiffres',
        ],

        /*
            |--------------------------------------------------------------------------
            | Validation Messages
            |--------------------------------------------------------------------------
            */
        'messages' => [
            'invalid_format' => 'Le format du téléphone n\'est pas valide pour le pays :country.',
            'invalid_length' => 'Le téléphone doit avoir entre :min et :max chiffres pour le pays :country.',
            'unknown_country' => 'Format de pays non reconnu. Utilisez un code pays ISO à 2 lettres.',
        ],
    ],
];
