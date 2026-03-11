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
                'version_code' => 11,
                'version_name' => '1.0.11',
                'update_required' => true,
                'release_notes' => 'Commandez et faites vous livrer à domicile',
                'app_link' => 'https://play.google.com/store/apps/details?id=cm.petrolex.isogaz_customer_app',
            ]
        );

        AppVersion::updateOrCreate(
            ['app_type' => 'customer_app', 'platform' => 'ios'],
            [
                'version_code' => 11,
                'version_name' => '1.0.11',
                'update_required' => true,
                'release_notes' => 'Commandez et faites vous livrer à domicile',
                'app_link' => 'https://apps.apple.com/app/com.isogaz.customer_app',
            ]
        );

        // Delivery App Versions
        AppVersion::updateOrCreate(
            ['app_type' => 'delivery_app', 'platform' => 'android'],
            [
                'version_code' => 10,
                'version_name' => '1.0.10',
                'update_required' => true,
                'release_notes' => 'Tracking et livraison des commandes',
                'app_link' => 'https://play.google.com/store/apps/details?id=cm.petrolex.isogaz_delivery_app',
            ]
        );

        AppVersion::updateOrCreate(
            ['app_type' => 'delivery_app', 'platform' => 'ios'],
            [
                'version_code' => 10,
                'version_name' => '1.0.10',
                'update_required' => true,
                'release_notes' => 'Tracking et livraison des commandes',
                'app_link' => 'https://apps.apple.com/app/com.isogaz.delivery_app',
            ]
        );
    }
}
