<?php

namespace Database\Seeders\Development;

use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\BottleMovement;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\ProductCategory;
use App\Models\ProductCategoryDistributionCenter;
use App\Models\User;
use Database\Factories\ProductFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BottleSeeder extends Seeder
{
    private Collection $managers;
    private Collection $gasManagers;
    private Collection $bottleTypes;
    private ProductFactory $productFactory;

    public function run(): void
    {
        $this->command->info('Creating bottles for development...');

        if (! $this->checkPivotTableExists()) {
            return;
        }

        $this->cacheFrequentlyUsedData();
        $this->createBottlesInStock();
        $this->createSpecialStatusBottles();

        $this->command->info('Development bottles created successfully!');
    }

    private function checkPivotTableExists(): bool
    {
        $pivotExists = DB::table('product_category_distribution_center')->count() > 0;
        if (! $pivotExists) {
            $this->command->error('Stock data not found. Run ProductCategoryDistributionCenterSeeder first.');

            return false;
        }

        return true;
    }

    private function cacheFrequentlyUsedData(): void
    {
        $this->managers = User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->get();
        $this->gasManagers = User::role([UserRole::MANAGER()->value, UserRole::GAS_MANAGER()->value])->get();
        $this->bottleTypes = BottleType::where('is_active', true)->get();
        $this->productFactory = new ProductFactory;

        if ($this->managers->isEmpty() || $this->gasManagers->isEmpty() || $this->bottleTypes->isEmpty()) {
            $this->command->warn('Some required data is missing (users or bottle types)');
        }
    }

    private function createBottlesInStock(): void
    {
        $distCenterStocks = ProductCategoryDistributionCenter::with([
            'productCategory',
            'distributionCenter',
        ])
            ->whereHas('productCategory', function ($query) {
                $query->bottles();
            })
            ->where(function ($query) {
                $query->where('stock_empty', '>', 0)
                    ->orWhere('stock_filled', '>', 0);
            })
            ->get();

        if ($distCenterStocks->isEmpty()) {
            $this->command->error('No distribution centers with bottle stock found.');

            return;
        }

        $totalEmptyCreated = 0;
        $totalFilledCreated = 0;
        $movements = [];

        foreach ($distCenterStocks as $stock) {
            if (! $this->validateStockData($stock)) {
                continue;
            }

            $productCategory = $stock->productCategory;
            $bottleType = $productCategory->productTypeInstance;
            $center = $stock->distributionCenter;

            $emptyCount = (int) $stock->stock_empty;
            $filledCount = (int) $stock->stock_filled;

            if ($emptyCount > 0) {
                $this->command->info("Creating {$emptyCount} empty {$bottleType->name} bottles for {$center->name}");
                $this->createBottleBatch($productCategory, $bottleType, $center, $emptyCount, false, $movements);
                $totalEmptyCreated += $emptyCount;
            }

            if ($filledCount > 0) {
                $this->command->info("Creating {$filledCount} filled {$bottleType->name} bottles for {$center->name}");
                $this->createBottleBatch($productCategory, $bottleType, $center, $filledCount, true, $movements);
                $totalFilledCreated += $filledCount;
            }
        }

        $this->insertMovements($movements);
        $this->command->info("Total bottles created in stock: {$totalEmptyCreated} empty and {$totalFilledCreated} filled");
    }

    private function validateStockData($stock): bool
    {
        if (! $stock->productCategory) {
            $this->command->warn("ProductCategory not found for stock record #{$stock->id}. Skipping.");

            return false;
        }

        if (! $stock->productCategory->productTypeInstance) {
            $this->command->warn("ProductType not found for ProductCategory #{$stock->productCategory->id}. Skipping.");

            return false;
        }

        if (! $stock->distributionCenter) {
            $this->command->warn("DistributionCenter not found for stock record #{$stock->id}. Skipping.");

            return false;
        }

        return true;
    }

    private function createBottleBatch($productCategory, $bottleType, $center, $count, $isFilled, &$movements): void
    {
        for ($i = 0; $i < $count; $i++) {
            $product = $this->productFactory->bottle(
                productCategoryId: $productCategory->id,
                bottleTypeId: $bottleType->id,
                distributionCenterId: $center->id,
                bottleAttributes: [
                    'is_filled' => $isFilled,
                    'status' => BottleStatus::IN_STOCK(),
                ]
            )->create();

            $movements[] = [
                'bottle_id' => $product->bottle->id,
                'user_id' => $this->managers->random()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                'notes' => $isFilled ?
                    'Filled bottle received from supplier and put in stock' :
                    'Empty bottle received from supplier and put in stock',
                'created_at' => now()->subDays(rand(5, 60)),
                'updated_at' => now(),
            ];
        }
    }

    private function createSpecialStatusBottles(): void
    {
        $centers = DistributionCenter::where('is_active', true)->get();
        $totalSpecialCreated = 0;
        $movements = [];

        foreach ($centers as $center) {
            $lostCount = fake()->numberBetween(2, 5);
            $returnedCount = fake()->numberBetween(2, 8);

            $this->command->info("Creating {$lostCount} lost/stolen bottles for {$center->name}");
            $this->createLostStolenBottles($center, $lostCount, $movements);
            $totalSpecialCreated += $lostCount;

            $this->command->info("Creating {$returnedCount} returned-to-supplier bottles for {$center->name}");
            $this->createReturnedBottles($center, $returnedCount, $movements);
            $totalSpecialCreated += $returnedCount;
        }

        $this->insertMovements($movements);
        $this->command->info("Total special status bottles created: {$totalSpecialCreated}");
    }

    private function createLostStolenBottles($center, $count, &$movements): void
    {
        for ($i = 0; $i < $count; $i++) {
            $bottleType = $this->bottleTypes->random();
            $productCategory = $this->findProductCategory($bottleType->id);

            if (! $productCategory) {
                continue;
            }

            $product = $this->productFactory->bottle(
                productCategoryId: $productCategory->id,
                bottleTypeId: $bottleType->id,
                distributionCenterId: $center->id,
                bottleAttributes: [
                    'is_filled' => fake()->boolean(),
                    'status' => BottleStatus::LOST_STOLEN(),
                ]
            )->create();

            $movements[] = [
                'bottle_id' => $product->bottle->id,
                'user_id' => $this->managers->random()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                'notes' => 'Bottle received from supplier and put in stock',
                'created_at' => now()->subMonths(rand(6, 12)),
                'updated_at' => now(),
            ];

            $movements[] = [
                'bottle_id' => $product->bottle->id,
                'user_id' => $this->managers->random()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::DECLARE_LOST_STOLEN(),
                'notes' => fake()->randomElement([
                    'Bottle declared lost during last inventory',
                    'Bottle not found after delivery',
                    'Reported as stolen by customer',
                    'Disappearance noticed at depot',
                ]),
                'created_at' => now()->subMonths(rand(1, 6)),
                'updated_at' => now(),
            ];
        }
    }

    private function createReturnedBottles($center, $count, &$movements): void
    {
        for ($i = 0; $i < $count; $i++) {
            $bottleType = $this->bottleTypes->random();
            $productCategory = $this->findProductCategory($bottleType->id);

            if (! $productCategory) {
                continue;
            }

            $product = $this->productFactory->bottle(
                productCategoryId: $productCategory->id,
                bottleTypeId: $bottleType->id,
                distributionCenterId: $center->id,
                bottleAttributes: [
                    'is_filled' => false,
                    'status' => BottleStatus::RETURNED_TO_SUPPLIER(),
                ]
            )->create();

            $movements[] = [
                'bottle_id' => $product->bottle->id,
                'user_id' => $this->managers->random()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                'notes' => 'Bottle received from supplier and put in stock',
                'created_at' => now()->subMonths(rand(6, 12)),
                'updated_at' => now(),
            ];

            $movements[] = [
                'bottle_id' => $product->bottle->id,
                'user_id' => $this->gasManagers->random()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::RETURN_TO_SUPPLIER(),
                'notes' => fake()->randomElement([
                    'Defective bottle returned to supplier',
                    'Bottle with damaged valve',
                    'Returned due to corrosion',
                    'Too old bottle, returned for renewal',
                ]),
                'created_at' => now()->subMonths(rand(1, 3)),
                'updated_at' => now(),
            ];
        }
    }

    private function findProductCategory(int $bottleTypeId): ?ProductCategory
    {
        return ProductCategory::where('product_type', ProductType::BOTTLE())
            ->where('product_type_id', $bottleTypeId)
            ->first();
    }

    private function insertMovements(array $movements): void
    {
        if (! empty($movements)) {
            BottleMovement::insert($movements);
        }
    }
}
