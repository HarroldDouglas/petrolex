<?php

namespace Database\Seeders\Production;

use App\Enums\ProductType;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating product categories...');

        $this->createBottleProductCategories();
        $this->createAccessoryProductCategories();
        $this->linkProductCategoriesToDistributionCenters();

        $this->command->info('Product categories created successfully!');
    }

    /**
     * Create product categories for bottle types
     */
    private function createBottleProductCategories(): void
    {
        $bottleTypes = BottleType::all();

        if ($bottleTypes->count() === 0) {
            $this->command->warn('No bottle types found. Run BottleTypeSeeder first.');

            return;
        }

        $categoryCount = 0;

        foreach ($bottleTypes as $bottleType) {
            ProductCategory::firstOrCreate([
                'product_type' => ProductType::BOTTLE(),
                'product_type_id' => $bottleType->id,
            ]);

            $categoryCount++;
        }

        $this->command->info("{$categoryCount} bottle product categories created.");
    }

    /**
     * Create product categories for accessory types
     */
    private function createAccessoryProductCategories(): void
    {
        $accessoryTypes = AccessoryType::all();

        if ($accessoryTypes->count() === 0) {
            $this->command->warn('No accessory types found. Run AccessoryTypeSeeder first.');

            return;
        }

        $categoryCount = 0;

        foreach ($accessoryTypes as $accessoryType) {
            ProductCategory::firstOrCreate([
                'product_type' => ProductType::ACCESSORY(),
                'product_type_id' => $accessoryType->id,
            ]);

            $categoryCount++;
        }

        $this->command->info("{$categoryCount} accessory product categories created.");
    }

    /**
     * Link product categories to distribution centers with appropriate stock levels
     */
    private function linkProductCategoriesToDistributionCenters(): void
    {
        $this->command->info('Linking product categories to distribution centers...');

        // Use the update method to avoid inserting duplicate records
        $this->command->call('db:seed', [
            '--class' => 'Database\\Seeders\\Development\\ProductCategoryDistributionCenterSeeder',
        ]);
    }
}
