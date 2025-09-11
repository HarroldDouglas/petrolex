<?php

// database/seeders/Production/BottleTypeSeeder.php

namespace Database\Seeders\Production;

use App\Models\BottleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

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
                'name_en' => '6Kg Gas Bottle',
                'description' => 'Bouteille standard de 6kg pour usage domestique',
                'description_en' => 'Standard 6kg bottle for domestic use',
                'capacity' => '6',
                'height' => 40.0,
                'weight' => 16.0,
                'radius' => 15.5,
                'content_price' => 3900,
                'full_price' => 5000,
                'is_active' => true,
            ],
            [
                'name' => 'Bouteille de 9Kg',
                'name_en' => '9Kg Gas Bottle',
                'description' => 'Bouteille moyenne de 9kg pour usage régulier',
                'description_en' => 'Medium 9kg bottle for regular use',
                'capacity' => '9',
                'height' => 45.0,
                'weight' => 16.0,
                'radius' => 17.5,
                'content_price' => 6000,
                'full_price' => 6500,
                'is_active' => true,
            ],
            [
                'name' => 'Bouteille de 12Kg',
                'name_en' => '12Kg Gas Bottle',
                'description' => 'Bouteille moyenne de 12kg pour usage régulier',
                'description_en' => 'Medium 12kg bottle for regular use',
                'capacity' => '12',
                'height' => 50.0,
                'weight' => 16.0,
                'radius' => 20.5,
                'content_price' => 7800,
                'full_price' => 8500,
                'is_active' => true,
            ],
        ];

        foreach ($bottleTypes as $bottleType) {
            $bottle = BottleType::firstOrCreate(
                ['name' => $bottleType['name']],
                $bottleType
            );

            // Add images to bottle types
            $this->addBottleImages($bottle);
        }

        $this->command->info('Bottle types created successfully!');
    }

    /**
     * Add images to bottle types
     */
    private function addBottleImages(BottleType $bottle): void
    {
        $imagesPath = public_path('assets/images/mobile/products/bottles');

        // Check if images directory exists
        if (! File::exists($imagesPath)) {
            $this->command->warn("Images directory not found: {$imagesPath}");
            $this->command->info("Skipping image addition for {$bottle->name}");

            return;
        }

        // Map bottle types to their corresponding images
        $imageMapping = [
            'Bouteille de 6Kg' => [
                'bouteille_gaz_6kg_1.jpeg',
                'bouteille_gaz_6kg_2.jpeg',
            ],
            'Bouteille de 12Kg' => [
                'bouteille_gaz_12kg_1.jpeg',
                'bouteille_gaz_12kg_2.jpeg',
            ],
            // For 9Kg bottles, we now have a specific 9kg image
            'Bouteille de 9Kg' => [
                'bouteille_gaz_9kg_1.jpeg',
            ],
        ];

        if (isset($imageMapping[$bottle->name])) {
            $images = $imageMapping[$bottle->name];
            $addedCount = 0;

            foreach ($images as $imageName) {
                $imagePath = $imagesPath.'/'.$imageName;

                if (File::exists($imagePath)) {
                    // Check if image is already attached to avoid duplicates
                    $existingMedia = $bottle->getMedia('images')->where('name', $imageName)->first();

                    if (! $existingMedia) {
                        // Copy the file to preserve the original
                        $bottle->addMedia($imagePath)
                            ->preservingOriginal()
                            ->usingName($imageName)
                            ->toMediaCollection('images');

                        $addedCount++;
                        $this->command->info("Added image {$imageName} to {$bottle->name}");
                    }
                } else {
                    $this->command->warn("Image not found: {$imagePath}");
                }
            }

            if ($addedCount === 0) {
                $this->command->info("No new images added to {$bottle->name} (images may already exist or files not found)");
            }
        } else {
            $this->command->info("No image mapping defined for {$bottle->name}");
        }
    }
}
