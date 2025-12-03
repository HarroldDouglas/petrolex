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
        ];

        foreach ($bottleTypes as $bottleType) {
            $bottle = BottleType::firstOrCreate(
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
        $imagesPath = public_path('zip/PRODUITS/Gadgets');

        if (! File::exists($imagesPath)) {
            $this->command->warn("Images directory not found: {$imagesPath}");
            $this->command->info("Skipping image addition for {$bottle->name}");

            return;
        }

        $imageMapping = [
            'Bouteille de 9Kg' => [
                'bouteille_de_gaz.jpg',
            ],
        ];

        if (isset($imageMapping[$bottle->name])) {
            $images = $imageMapping[$bottle->name];
            $addedCount = 0;

            foreach ($images as $imageName) {
                $imagePath = $imagesPath.'/'.$imageName;

                if (File::exists($imagePath)) {
                    $existingMedia = $bottle->getMedia('images')->where('name', $imageName)->first();

                    if (! $existingMedia) {
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
