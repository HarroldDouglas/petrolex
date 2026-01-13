<?php

namespace Database\Seeders;

use App\Models\AppVersion;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Customer App Versions
        AppVersion::updateOrCreate(
            ['app_type' => 'customer_app', 'platform' => 'android'],
            [
                'version_code' => 2,
                'version_name' => '1.0.0',
                'update_required' => true,
                'release_notes' => 'Integration du Wallet',
                'app_link' => 'https://play.google.com/store/apps/details?id=cm.petrolex.isogaz',
            ]
        );

        AppVersion::updateOrCreate(
            ['app_type' => 'customer_app', 'platform' => 'ios'],
            [
                'version_code' => 2,
                'version_name' => '1.0.0',
                'update_required' => false,
                'release_notes' => 'Integration du Wallet',
                'app_link' => 'https://apps.apple.com/app/cm.petrolex.isogaz',
            ]
        );

        // Delivery App Versions
        AppVersion::updateOrCreate(
            ['app_type' => 'delivery_app', 'platform' => 'android'],
            [
                'version_code' => 2,
                'version_name' => '1.0.0',
                'update_required' => true,
                'release_notes' => 'Modification logos',
                'app_link' => 'https://play.google.com/store/apps/details?id=cm.petrolex.isogaz_delivery',
            ]
        );

        AppVersion::updateOrCreate(
            ['app_type' => 'delivery_app', 'platform' => 'ios'],
            [
                'version_code' => 2,
                'version_name' => '1.0.0',
                'update_required' => false,
                'release_notes' => 'Modification logos',
                'app_link' => 'https://apps.apple.com/app/cm.petrolex.isogaz_delivery',
            ]
        );
    }
}
