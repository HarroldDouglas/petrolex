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

        // Real specifications from client - DO NOT modify without client approval
        $bottleTypes = [
            [
                'name' => 'Bouteille de 9Kg',
                'name_en' => '9Kg Gas Bottle',
                'description' => 'Bouteille de gaz butane de 9kg',
                'description_en' => '9kg butane gas bottle',
                'content_type' => 'BUTANE',
                'capacity' => '18.5',           // Volume: 18.5 L
                'height' => 461,                // Hauteur: 461 mm
                'weight' => 9,                  // Masse du contenant: 9 kg
                'test_pressure' => '30 BAR',    // Test Pressure: 30 BAR
                'radius' => 0,
                'content_price' => 4680,        // Prix de la recharge: 4680 FCFA
                'full_price' => 21780,          // Prix consigne + recharge: 21780 FCFA
                'is_active' => true,
                'specifications' => [
                    ['name' => 'Contenant', 'value' => 'BUTANE'],
                    ['name' => 'Masse du contenant', 'value' => '9 kg'],
                    ['name' => 'Volume', 'value' => '18.5 L'],
                    ['name' => 'Test Pressure', 'value' => '30 BAR'],
                    ['name' => 'Hauteur', 'value' => '461 mm'],
                ],
            ],
        ];

        foreach ($bottleTypes as $bottleType) {
            $bottle = BottleType::updateOrCreate(
                ['name' => $bottleType['name']],
                $bottleType
            );

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

        if (! File::exists($imagesPath)) {
            $this->command->warn("Images directory not found: {$imagesPath}");
            $this->command->info("Skipping image addition for {$bottle->name}");

            return;
        }

        $imageMapping = [
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
                    try {
                        $existingMedia = $bottle->getMedia('images')->where('name', $imageName)->first();

                        if (! $existingMedia) {
                            $bottle->addMedia($imagePath)
                                ->preservingOriginal()
                                ->usingName($imageName)
                                ->toMediaCollection('images');

                            $addedCount++;
                            $this->command->info("Added image {$imageName} to {$bottle->name}");
                        }
                    } catch (\Exception $e) {
                        $this->command->warn("Could not add image {$imageName}: {$e->getMessage()}");
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
