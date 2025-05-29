<?php

// database/seeders/Development/AccessorySeeder.php

namespace Database\Seeders\Development;

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

        foreach ($centers as $center) {
            $this->createAccessoriesForCenter($center, $accessoryTypes);
        }

        $this->command->info('Development accessories created successfully!');
    }

    /**
     * Create accessories for a distribution center
     */
    private function createAccessoriesForCenter(DistributionCenter $center, $accessoryTypes): void
    {
        $this->command->info("Creating accessories for {$center->name}...");

        foreach ($accessoryTypes as $accessoryType) {
            $quantity = fake()->numberBetween(10, 50);

            $product = Product::factory()
                ->accessory($accessoryType->id, $center->id, [
                    'sku' => 'ACC-'.strtoupper(Str::random(6)),
                    'quantity' => $quantity,
                ])
                ->create();

            $this->command->info("{$quantity} {$accessoryType->name} accessories created for {$center->name}");
        }
    }
}
