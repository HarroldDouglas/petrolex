<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Advertising Banners Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains the advertising banners that will be
    | displayed in the mobile application through the API.
    |
    */

    'banners' => [
        [
            'id' => 1,
            'title' => 'Bannière principale',
            'description' => 'Découvrez nos services de livraison de gaz',
            'image_url' => 'zip/BANIERES/banniere5 new.png',
            'action_url' => null,
            'is_active' => true,
            'priority' => 1,
            'start_date' => '2025-01-01',
            'end_date' => '2026-12-31',
        ],
        [
            'id' => 2,
            'title' => 'Bannière promotionnelle',
            'description' => 'Profitez de nos offres spéciales',
            'image_url' => 'zip/BANIERES/banniere4 new.png',
            'action_url' => null,
            'is_active' => true,
            'priority' => 2,
            'start_date' => '2025-01-01',
            'end_date' => '2026-12-31',
        ],
    ],
];
