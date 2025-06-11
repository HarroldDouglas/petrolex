<?php

// database/seeders/Production/BottleTypeSeeder.php

namespace Database\Seeders\Production;

use App\Models\BottleType;
use Illuminate\Database\Seeder;

class BottleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating bottle types...');

        $bottleTypes = [
            [
                'name' => 'Bouteille de 6Kg',
                'description' => 'Bouteille standard de 6kg pour usage domestique',
                'capacity' => '6',
                'height' => 40.0,
                'weight' => 16.0,
                'radius' => 15.5,
                'content_price' => 3900,
                'bottle_with_content_price' => 5000,
                'is_active' => true,
            ],
            [
                'name' => 'Bouteille de 9Kg',
                'description' => 'Bouteille moyenne de 9kg pour usage régulier',
                'capacity' => '9',
                'height' => 45.0,
                'weight' => 16.0,
                'radius' => 17.5,
                'content_price' => 6000,
                'bottle_with_content_price' => 6500,
                'is_active' => true,
            ],
            [
                'name' => 'Bouteille de 12Kg',
                'description' => 'Bouteille moyenne de 12kg pour usage régulier',
                'capacity' => '12',
                'height' => 50.0,
                'weight' => 16.0,
                'radius' => 20.5,
                'content_price' => 7800,
                'bottle_with_content_price' => 8500,
                'is_active' => true,
            ],
        ];

        foreach ($bottleTypes as $bottleType) {
            BottleType::firstOrCreate(
                ['name' => $bottleType['name']],
                $bottleType
            );
        }

        $this->command->info('Bottle types created successfully!');
    }
}
