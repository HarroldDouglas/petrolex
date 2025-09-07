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
            'title' => 'Bannière 1',
            'description' => 'Description bannière 1',
            'image_url' => 'assets/images/mobile/banners/banner_1.png',
            'action_url' => null,
            'is_active' => true,
            'priority' => 1,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ],
        [
            'id' => 2,
            'title' => 'Bannière 2',
            'description' => 'Description bannière 2',
            'image_url' => 'assets/images/mobile/banners/banner_2.jpeg',
            'action_url' => null,
            'is_active' => true,
            'priority' => 2,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
        ],
    ],
];
