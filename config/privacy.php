<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | These values are used in the privacy policy page.
    | You can modify them in your .env file.
    |
    */

    'company_name' => env('PRIVACY_COMPANY_NAME', config('app.name')),
    'app_description' => env('PRIVACY_APP_DESCRIPTION', 'Application de livraison'),

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'email' => env('PRIVACY_CONTACT_EMAIL', env('MAIL_FROM_ADDRESS', 'contact@example.com')),
        'phone' => env('PRIVACY_CONTACT_PHONE', '+237 XXX XXX XXX'),
        'address' => env('PRIVACY_CONTACT_ADDRESS', 'Cameroun'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Privacy Policy URL
    |--------------------------------------------------------------------------
    |
    | The public URL where the privacy policy is accessible
    |
    */

    'url' => env('PRIVACY_POLICY_URL', '/privacy-policy'),
];
