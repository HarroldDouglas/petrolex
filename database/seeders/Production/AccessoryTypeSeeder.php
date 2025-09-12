<?php

// database/seeders/Production/AccessoryTypeSeeder.php

namespace Database\Seeders\Production;

use App\Models\AccessoryType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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
            $accessory = AccessoryType::firstOrCreate(
                ['name' => $accessoryType['name']],
                $accessoryType
            );

            $this->addAccessoryImages($accessory);
        }

        $this->command->info('Accessory types created successfully!');
    }

    /**
     * Add images to accessory types
     */
    private function addAccessoryImages(AccessoryType $accessory): void
    {
        $imagesPath = public_path('assets/images/mobile/products/accessories');

        if (! File::exists($imagesPath)) {
            $this->command->warn("Images directory not found: {$imagesPath}");
            $this->command->info("Skipping image addition for {$accessory->name}");

            return;
        }

        $imageMapping = [
            'Tuyau de gaz standard 5m' => [
                'tuyau_gaz_standard_1.jpeg',
            ],
            'Détendeur universel' => [
                'detendeur_universel_1.jpeg',
            ],
            'Protection anti-chute' => [
                'protection_anti_chute_1.jpeg',
            ],
            'Adaptateur pour réchaud' => [
                'adaptateur_pour_rechaud_1.jpeg',
            ],
        ];

        if (isset($imageMapping[$accessory->name])) {
            $images = $imageMapping[$accessory->name];
            $addedCount = 0;

            foreach ($images as $imageName) {
                $imagePath = $imagesPath.'/'.$imageName;

                if (File::exists($imagePath)) {
                    $existingMedia = $accessory->getMedia('images')->where('name', $imageName)->first();

                    if (! $existingMedia) {
                        $accessory->addMedia($imagePath)
                            ->preservingOriginal()
                            ->usingName($imageName)
                            ->toMediaCollection('images');

                        $addedCount++;
                        $this->command->info("Added image {$imageName} to {$accessory->name}");
                    }
                } else {
                    $this->command->warn("Image not found: {$imagePath}");
                }
            }

            if ($addedCount === 0) {
                $this->command->info("No new images added to {$accessory->name} (images may already exist or files not found)");
            }
        } else {
            $this->command->info("No image mapping defined for {$accessory->name}");
        }
    }
}
