<?php

// database/seeders/Production/AccessoryTypeSeeder.php

namespace Database\Seeders\Production;

use App\Models\AccessoryType;
use Illuminate\Database\Seeder;

class AccessoryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating accessory types...');

        $accessoryTypes = [
            [
                'name' => 'Tuyau de gaz standard 5m',
                'name_en' => 'Standard 5m Gas Hose',
                'price' => 2500,
                'description' => 'Tuyau flexible pour connecter la bouteille de gaz aux appareils',
                'description_en' => 'Flexible hose to connect gas bottle to appliances',
                'is_active' => true,
            ],
            [
                'name' => 'Détendeur universel',
                'name_en' => 'Universal Regulator',
                'price' => 1500,
                'description' => 'Détendeur compatible avec la plupart des bouteilles de gaz',
                'description_en' => 'Regulator compatible with most gas bottles',
                'is_active' => true,
            ],
            [
                'name' => 'Protection anti-chute',
                'name_en' => 'Fall Protection',
                'price' => 500,
                'description' => 'Protection pour éviter la chute des bouteilles',
                'description_en' => 'Protection to prevent bottle falls',
                'is_active' => true,
            ],
            [
                'name' => 'Adaptateur pour réchaud',
                'name_en' => 'Stove Adapter',
                'price' => 2000,
                'description' => 'Adaptateur spécifique pour connexion aux réchauds',
                'description_en' => 'Specific adapter for stove connection',
                'is_active' => true,
            ],
        ];

        foreach ($accessoryTypes as $accessoryType) {
            AccessoryType::firstOrCreate(
                ['name' => $accessoryType['name']],
                $accessoryType
            );
        }

        $this->command->info('Accessory types created successfully!');
    }
}
