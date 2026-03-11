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
                'name' => 'Brûleur à gaz avec soupape en laiton',
                'name_en' => 'Gas burner with brass valve',
                'price' => 2000,
                'description' => 'Brûleur à gaz de haute qualité avec soupape en laiton pour une meilleure durabilité et sécurité.',
                'description_en' => 'High quality gas burner with brass valve for better durability and safety.',
                'is_active' => true,
                'images' => ['image9.jpg.jpeg'],
            ],
            [
                'name' => 'Support en fer noir (foyer)',
                'name_en' => 'Black iron stand (stove)',
                'price' => 4500,
                'description' => 'Support en fer noir robuste pour cylindre de 6kg. Idéal pour une utilisation domestique.',
                'description_en' => 'Sturdy black iron stand for 6kg cylinder. Ideal for domestic use.',
                'is_active' => true,
                'images' => ['image7.jpg.jpeg'],
            ],
            [
                'name' => 'Régulateur de gaz avec clé (détendeur)',
                'name_en' => 'Gas regulator with key (pressure regulator)',
                'price' => 1500,
                'description' => 'Régulateur de gaz avec clé de serrage. Taille : sortie de 8mm.',
                'description_en' => 'Gas regulator with tightening key. Size: 8mm outlet.',
                'is_active' => true,
                'images' => ['image10.jpg.jpeg'],
            ],
            [
                'name' => 'Tuyau de gaz en PVC blanc 2m',
                'name_en' => 'White PVC gas hose 2m',
                'price' => 2000,
                'description' => 'Tuyau de gaz en PVC blanc. Taille : 8x14mm x 2.0m.',
                'description_en' => 'White PVC gas hose. Size: 8x14mm x 2.0m.',
                'is_active' => true,
                'images' => ['image8.jpg.jpeg'],
            ],
            [
                'name' => 'Tuyau de gaz en PVC blanc 1.5m',
                'name_en' => 'White PVC gas hose 1.5m',
                'price' => 1500,
                'description' => 'Tuyau de gaz en PVC blanc. Taille : 8x14mm x 1.5m.',
                'description_en' => 'White PVC gas hose. Size: 8x14mm x 1.5m.',
                'is_active' => true,
                'images' => ['image11.jpg.jpeg'],
            ],
        ];

        foreach ($accessoryTypes as $accessoryTypeData) {
            $images = $accessoryTypeData['images'] ?? [];
            unset($accessoryTypeData['images']);

            $accessory = AccessoryType::updateOrCreate(
                ['name' => $accessoryTypeData['name']],
                $accessoryTypeData
            );

            $accessory->clearMediaCollection('images');
            $this->addAccessoryImages($accessory, $images);
        }

        $this->command->info('Accessory types created successfully! Total: '.count($accessoryTypes));
    }

    /**
     * Add images to accessory types
     */
    private function addAccessoryImages(AccessoryType $accessory, array $imageNames): void
    {
        $imagesPath = public_path('assets/products');

        if (! File::exists($imagesPath)) {
            $this->command->warn("Images directory not found: {$imagesPath}");
            $this->command->info("Skipping image addition for {$accessory->name}");

            return;
        }

        if (empty($imageNames)) {
            $this->command->info("No images defined for {$accessory->name}");

            return;
        }

        $addedCount = 0;

        foreach ($imageNames as $imageName) {
            $imagePath = $imagesPath.'/'.$imageName;

            if (File::exists($imagePath)) {
                try {
                    $existingMedia = $accessory->getMedia('images')->where('name', pathinfo($imageName, PATHINFO_FILENAME))->first();

                    if (! $existingMedia) {
                        $accessory->addMedia($imagePath)
                            ->preservingOriginal()
                            ->usingName(pathinfo($imageName, PATHINFO_FILENAME))
                            ->toMediaCollection('images');

                        $addedCount++;
                        $this->command->info("  ✓ Added image: {$imageName}");
                    }
                } catch (\Exception $e) {
                    $this->command->warn("  ✗ Could not add image {$imageName}: {$e->getMessage()}");
                }
            } else {
                $this->command->warn("  ✗ Image not found: {$imagePath}");
            }
        }

        $this->command->info("  → {$addedCount} images added to {$accessory->name}");
    }
}
