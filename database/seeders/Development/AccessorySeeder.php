<?php

// database/seeders/Development/AccessorySeeder.php

namespace Database\Seeders\Development;

use App\Enums\ProductType;
use App\Models\Accessory;
use App\Models\AccessoryType;
use App\Models\DistributionCenter;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AccessorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating accessories for development...');

        $accessoryTypes = AccessoryType::all();
        $centers = DistributionCenter::all();

        if ($accessoryTypes->isEmpty()) {
            $this->command->error('No accessory types found. Run AccessoryTypeSeeder first.');

            return;
        }

        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $accessoryProducts = Product::where('product_type', ProductType::ACCESSORY())->get();

        if ($accessoryProducts->isEmpty()) {
            $this->command->error('No accessory products found. Run ProductSeeder first.');

            return;
        }

        $productMapping = [];

        foreach ($accessoryTypes as $index => $accessoryType) {
            if (isset($accessoryProducts[$index])) {
                $productMapping[$accessoryType->id] = $accessoryProducts[$index]->id;
            }
        }

        foreach ($centers as $center) {
            $this->createAccessoriesForCenter($center, $accessoryTypes, $productMapping);
        }

        $this->command->info('Development accessories created successfully!');
    }

    /**
     * Create accessories for a distribution center
     */
    private function createAccessoriesForCenter(DistributionCenter $center, $accessoryTypes, $productMapping): void
    {
        $this->command->info("Creating accessories for {$center->name}...");

        foreach ($accessoryTypes as $accessoryType) {
            if (! isset($productMapping[$accessoryType->id])) {
                $this->command->warn("No product found for accessory type {$accessoryType->name}. Skipping.");
                continue;
            }

            $quantity = fake()->numberBetween(10, 50);

            Accessory::create([
                'product_id' => $productMapping[$accessoryType->id], // Utiliser le mapping pour trouver le bon product_id
                'accessory_type_id' => $accessoryType->id,
                'distribution_center_id' => $center->id,
                'sku' => 'ACC-'.strtoupper(Str::random(6)),
                'quantity' => $quantity,
            ]);

            $this->command->info("{$quantity} {$accessoryType->name} accessories created for {$center->name}");
        }
    }
}
