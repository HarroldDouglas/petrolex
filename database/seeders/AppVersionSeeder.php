<?php

namespace Database\Seeders;

use App\Enums\AppType;
use App\Enums\Platform;
use App\Models\AppVersion;
use Illuminate\Database\Seeder;

class AppVersionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $versions = [
            // Customer App Versions
            [
                'app_type' => AppType::CUSTOMER_APP()->value,
                'platform' => Platform::ANDROID()->value,
                'version_code' => 1,
                'version_name' => '1.0.0',
                'update_required' => false,
                'release_notes' => 'Version initiale de l\'application client. Commandez du gaz en toute simplicité.',
                'is_active' => true,
            ],
            [
                'app_type' => AppType::CUSTOMER_APP()->value,
                'platform' => Platform::IOS()->value,
                'version_code' => 1,
                'version_name' => '1.0.0',
                'update_required' => false,
                'release_notes' => 'Version initiale de l\'application client. Commandez du gaz en toute simplicité.',
                'is_active' => true,
            ],

            // Delivery App Versions
            [
                'app_type' => AppType::DELIVERY_APP()->value,
                'platform' => Platform::ANDROID()->value,
                'version_code' => 1,
                'version_name' => '1.0.0',
                'update_required' => false,
                'release_notes' => 'Version initiale de l\'application livreur. Gérez vos livraisons efficacement.',
                'is_active' => true,
            ],
            [
                'app_type' => AppType::DELIVERY_APP()->value,
                'platform' => Platform::IOS()->value,
                'version_code' => 1,
                'version_name' => '1.0.0',
                'update_required' => false,
                'release_notes' => 'Version initiale de l\'application livreur. Gérez vos livraisons efficacement.',
                'is_active' => true,
            ],
        ];

        foreach ($versions as $version) {
            AppVersion::updateOrCreate(
                [
                    'app_type' => $version['app_type'],
                    'platform' => $version['platform'],
                    'version_code' => $version['version_code'],
                ],
                $version
            );
        }
    }
}
