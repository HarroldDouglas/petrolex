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
                'price' => 2500,
                'description' => 'Tuyau flexible pour connecter la bouteille de gaz aux appareils',
                'is_active' => true,
            ],
            [
                'name' => 'Détendeur universel',
                'price' => 1500,
                'description' => 'Détendeur compatible avec la plupart des bouteilles de gaz',
                'is_active' => true,
            ],
            [
                'name' => 'Protection anti-chute',
                'price' => 500,
                'description' => 'Protection pour éviter la chute des bouteilles',
                'is_active' => true,
            ],
            [
                'name' => 'Adaptateur pour réchaud',
                'price' => 2000,
                'description' => 'Adaptateur spécifique pour connexion aux réchauds',
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
