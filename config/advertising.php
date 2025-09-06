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
            'title' => 'Promo spéciale gaz domestique',
            'description' => 'Profitez de notre offre spéciale sur les bouteilles de gaz domestique',
            'image_url' => 'https://example.com/banners/promo-gaz-domestique.jpg',
            'action_url' => null,
            'is_active' => true,
            'priority' => 1,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ],
        [
            'id' => 2,
            'title' => 'Accessoires de sécurité',
            'description' => 'Découvrez notre gamme complète d\'accessoires de sécurité',
            'image_url' => 'https://example.com/banners/accessoires-securite.jpg',
            'action_url' => null,
            'is_active' => true,
            'priority' => 2,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ],
    ],
];