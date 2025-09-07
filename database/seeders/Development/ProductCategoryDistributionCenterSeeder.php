<?php

namespace Database\Seeders\Development;

use App\Enums\ProductType;
use App\Models\DistributionCenter;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategoryDistributionCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating product category to distribution center links...');

        $distributionCenters = DistributionCenter::all();

        if ($distributionCenters->count() === 0) {
            $this->command->warn('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $this->linkBottleCategoriesToCenters($distributionCenters);
        $this->linkAccessoryCategoriesToCenters($distributionCenters);

        $this->command->info('Product category to distribution center links created successfully!');
    }

    /**
     * Link bottle categories to distribution centers
     */
    private function linkBottleCategoriesToCenters($distributionCenters): void
    {
        $bottleCategories = ProductCategory::ofType(ProductType::BOTTLE())->get();

        if ($bottleCategories->count() === 0) {
            $this->command->warn('No bottle categories found. Run ProductCategorySeeder first.');

            return;
        }

        $linkCount = 0;

        foreach ($distributionCenters as $center) {
            $isFirstBottle = true;

            foreach ($bottleCategories as $category) {
                // Ensure first bottle has 0 stock (out of stock)
                if ($isFirstBottle) {
                    $stockEmpty = 0;
                    $stockFilled = 0;
                    $isFirstBottle = false;
                    $this->command->info("Setting bottle '{$category->name}' to 0 stock for center '{$center->name}'");
                } else {
                    // Generate random stock values for other bottles
                    $stockEmpty = rand(5, 30);
                    $stockFilled = rand(10, 50);
                }

                // Insert or update through the relationship to avoid duplicate records
                $center->productCategories()->syncWithoutDetaching([
                    $category->id => [
                        'stock_empty' => $stockEmpty,
                        'stock_filled' => $stockFilled,
                        'stock' => 0, // Bottles don't use this field
                    ],
                ]);

                $linkCount++;
            }
        }

        $this->command->info("{$linkCount} bottle category to distribution center links created.");
    }

    /**
     * Link accessory categories to distribution centers
     */
    private function linkAccessoryCategoriesToCenters($distributionCenters): void
    {
        $accessoryCategories = ProductCategory::ofType(ProductType::ACCESSORY())->get();

        if ($accessoryCategories->count() === 0) {
            $this->command->warn('No accessory categories found. Run ProductCategorySeeder first.');

            return;
        }

        $linkCount = 0;

        foreach ($distributionCenters as $center) {
            $isFirstAccessory = true;

            foreach ($accessoryCategories as $category) {
                // Ensure first accessory has 0 stock (out of stock)
                if ($isFirstAccessory) {
                    $stock = 0;
                    $isFirstAccessory = false;
                    $this->command->info("Setting accessory '{$category->name}' to 0 stock for center '{$center->name}'");
                } else {
                    // Generate random stock values for other accessories
                    $stock = rand(20, 100);
                }

                // Insert or update through the relationship to avoid duplicate records
                $center->productCategories()->syncWithoutDetaching([
                    $category->id => [
                        'stock' => $stock, // Accessories use this field
                        'stock_empty' => 0, // Accessories don't use these fields
                        'stock_filled' => 0,
                    ],
                ]);

                $linkCount++;
            }
        }

        $this->command->info("{$linkCount} accessory category to distribution center links created.");
    }
}
