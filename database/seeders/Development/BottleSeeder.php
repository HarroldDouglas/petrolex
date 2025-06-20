<?php

// database/seeders/Development/BottleSeeder.php

namespace Database\Seeders\Development;

use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\Bottle;
use App\Models\BottleMovement;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BottleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating bottles for development...');

        $bottleTypes = BottleType::all();
        $centers = DistributionCenter::all();

        if ($bottleTypes->isEmpty()) {
            $this->command->error('No bottle types found. Run BottleTypeSeeder first.');

            return;
        }

        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        // Make sure the pivot table exists and has data
        $pivotExists = DB::table('product_category_distribution_center')->count() > 0;
        if (! $pivotExists) {
            $this->command->error('Stock data not found. Run ProductCategoryDistributionCenterSeeder first.');

            return;
        }

        foreach ($centers as $center) {
            // Only BottleSeeder creates IN_STOCK, LOST_STOLEN, and RETURNED_TO_SUPPLIER bottles
            $this->createBottlesInStock($center, $bottleTypes);
            $this->createLostOrStolenBottles($center, $bottleTypes);
            $this->createReturnedToSupplierBottles($center, $bottleTypes);

            // Update the pivot table to reflect the actual bottle counts
            $this->updateStockCounts($center);
        }

        $this->command->info('Development bottles created successfully!');
    }

    /**
     * Get and display detailed stock statistics for a center
     */
    private function displayStockStatistics(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->line('--------------------------------------------------');
        $this->command->line("STOCK STATISTICS FOR: {$center->name}");
        $this->command->line('--------------------------------------------------');

        $totalPivotEmpty = 0;
        $totalPivotFilled = 0;
        $totalActualEmpty = 0;
        $totalActualFilled = 0;

        foreach ($bottleTypes as $bottleType) {
            // Get pivot values
            $pivotValues = $this->getPivotStockValues($center->id, $bottleType->id);
            $pivotEmpty = $pivotValues['empty'];
            $pivotFilled = $pivotValues['filled'];

            // Get actual values (calculated from the bottles table)
            $actualValues = $this->getActualStockValues($center->id, $bottleType->id);
            $actualEmpty = $actualValues['empty'];
            $actualFilled = $actualValues['filled'];

            // Calculate differences
            $emptyDiff = $actualEmpty - $pivotEmpty;
            $filledDiff = $actualFilled - $pivotFilled;

            // Format differences
            $emptyDiffFormatted = $this->formatDifference($emptyDiff);
            $filledDiffFormatted = $this->formatDifference($filledDiff);

            // Update totals
            $totalPivotEmpty += $pivotEmpty;
            $totalPivotFilled += $pivotFilled;
            $totalActualEmpty += $actualEmpty;
            $totalActualFilled += $actualFilled;

            // Display statistics for this bottle type
            $this->command->line("{$bottleType->name}:");
            $this->command->line("  - PIVOT   : {$pivotEmpty} empty, {$pivotFilled} filled");
            $this->command->line("  - BOTTLES : {$actualEmpty} empty {$emptyDiffFormatted}, {$actualFilled} filled {$filledDiffFormatted}");

            if ($emptyDiff !== 0 || $filledDiff !== 0) {
                $this->command->warn("  ⚠️ DIFFERENCE DETECTED for {$bottleType->name} in {$center->name}");
            }
        }

        // Display totals
        $totalEmptyDiff = $totalActualEmpty - $totalPivotEmpty;
        $totalFilledDiff = $totalActualFilled - $totalPivotFilled;
        $totalEmptyDiffFormatted = $this->formatDifference($totalEmptyDiff);
        $totalFilledDiffFormatted = $this->formatDifference($totalFilledDiff);

        $this->command->line('--------------------------------------------------');
        $this->command->line('TOTALS:');
        $this->command->line("  - PIVOT   : {$totalPivotEmpty} empty, {$totalPivotFilled} filled, Total: ".($totalPivotEmpty + $totalPivotFilled));
        $this->command->line("  - BOTTLES : {$totalActualEmpty} empty {$totalEmptyDiffFormatted}, {$totalActualFilled} filled {$totalFilledDiffFormatted}, Total: ".($totalActualEmpty + $totalActualFilled).' '.$this->formatDifference($totalEmptyDiff + $totalFilledDiff));

        if ($totalEmptyDiff !== 0 || $totalFilledDiff !== 0) {
            $this->command->warn("  ⚠️ TOTAL DIFFERENCE DETECTED for {$center->name}");
        }

        $this->command->line('--------------------------------------------------');
    }

    /**
     * Get stock values from the pivot table
     */
    private function getPivotStockValues(int $centerId, int $bottleTypeId): array
    {
        // Find the product category for this bottle type
        $productCategory = ProductCategory::where('product_type', ProductType::BOTTLE())
            ->where('product_type_id', $bottleTypeId)
            ->first();

        if (! $productCategory) {
            return [
                'empty' => 0,
                'filled' => 0,
            ];
        }

        $stockData = DB::table('product_category_distribution_center')
            ->where('distribution_center_id', $centerId)
            ->where('product_category_id', $productCategory->id)
            ->first();

        return [
            'empty' => $stockData ? (int) $stockData->stock_empty : 0,
            'filled' => $stockData ? (int) $stockData->stock_filled : 0,
        ];
    }

    /**
     * Get current stock values by counting bottles
     */
    private function getActualStockValues(int $centerId, int $bottleTypeId): array
    {
        $emptyCount = Bottle::where('bottle_type_id', $bottleTypeId)
            ->where('is_filled', false)
            ->where('status', BottleStatus::IN_STOCK())
            ->where('distribution_center_id', $centerId)
            ->count();

        $filledCount = Bottle::where('bottle_type_id', $bottleTypeId)
            ->where('is_filled', true)
            ->where('status', BottleStatus::IN_STOCK())
            ->where('distribution_center_id', $centerId)
            ->count();

        return [
            'empty' => $emptyCount,
            'filled' => $filledCount,
        ];
    }

    /**
     * Format a difference for display
     */
    private function formatDifference(int $diff): string
    {
        if ($diff === 0) {
            return '(identical)';
        }

        $sign = $diff > 0 ? '+' : '';

        return "({$sign}{$diff})";
    }

    /**
     * Create bottles in stock for a distribution center based on pivot table data
     */
    private function createBottlesInStock(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->info("Creating bottles in stock for {$center->name}...");

        // Counters for logs
        $totalEmptyCount = 0;
        $totalFilledCount = 0;
        $bottleTypeCounts = [];

        foreach ($bottleTypes as $bottleType) {
            // Find the product category for this bottle type
            $productCategory = ProductCategory::where('product_type', ProductType::BOTTLE())
                ->where('product_type_id', $bottleType->id)
                ->first();

            if (! $productCategory) {
                $this->command->warn("No product category found for bottle type {$bottleType->name}. Skipping.");
                continue;
            }

            // Get the stock counts from the pivot table
            $stockData = DB::table('product_category_distribution_center')
                ->where('distribution_center_id', $center->id)
                ->where('product_category_id', $productCategory->id)
                ->first();

            if (! $stockData) {
                $this->command->warn("No stock data found for {$bottleType->name} in {$center->name}. Skipping.");
                continue;
            }

            $emptyCount = (int) $stockData->stock_empty;
            $filledCount = (int) $stockData->stock_filled;

            $totalEmptyCount += $emptyCount;
            $totalFilledCount += $filledCount;

            // Create empty bottles
            for ($i = 0; $i < $emptyCount; $i++) {
                $product = Product::factory()
                    ->bottle($bottleType->id, $center->id, [
                        'is_filled' => false,
                        'status' => BottleStatus::IN_STOCK(),
                    ])
                    ->create();

                // Add a movement for stock entry
                BottleMovement::create([
                    'bottle_id' => $product->bottle->id,
                    'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                    'distribution_center_id' => $center->id,
                    'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                    'notes' => 'Empty bottle received from supplier and put in stock',
                    'created_at' => now()->subDays(rand(5, 60)),
                ]);
            }

            // Create filled bottles
            for ($i = 0; $i < $filledCount; $i++) {
                $product = Product::factory()
                    ->bottle($bottleType->id, $center->id, [
                        'is_filled' => true,
                        'status' => BottleStatus::IN_STOCK(),
                    ])
                    ->create();

                // Add a movement for stock entry
                BottleMovement::create([
                    'bottle_id' => $product->bottle->id,
                    'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                    'distribution_center_id' => $center->id,
                    'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                    'notes' => 'Filled bottle received from supplier and put in stock',
                    'created_at' => now()->subDays(rand(5, 60)),
                ]);
            }

            $this->command->info("{$emptyCount} empty and {$filledCount} filled {$bottleType->name} bottles created for {$center->name}");
        }
    }

    /**
     * Create lost or stolen bottles
     */
    private function createLostOrStolenBottles(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->info("Creating lost/stolen bottles for {$center->name}...");

        // Create a small number of lost/stolen bottles (between 1 and 5)
        $count = fake()->numberBetween(1, 5);

        for ($i = 0; $i < $count; $i++) {
            $bottleType = $bottleTypes->random();
            $isFilled = fake()->boolean();

            $product = Product::factory()
                ->bottle($bottleType->id, $center->id, [
                    'is_filled' => $isFilled,
                    'status' => BottleStatus::LOST_STOLEN(),
                ])
                ->create();

            // First, create the stock entry (initial state)
            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                'notes' => 'Bottle received from supplier and put in stock',
                'created_at' => now()->subMonths(rand(6, 12)),
            ]);

            // Then, record the loss/theft (final state)
            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::DECLARE_LOST_STOLEN(),
                'notes' => fake()->randomElement([
                    'Bottle declared lost during last inventory',
                    'Bottle not found after delivery',
                    'Reported as stolen by customer',
                    'Disappearance noticed at depot',
                ]),
                'created_at' => now()->subMonths(rand(1, 6)),
            ]);
        }

        $this->command->info("{$count} lost/stolen bottles created for {$center->name}");
    }

    /**
     * Create bottles returned to supplier
     */
    private function createReturnedToSupplierBottles(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->info("Creating returned-to-supplier bottles for {$center->name}...");

        $count = fake()->numberBetween(2, 8);

        for ($i = 0; $i < $count; $i++) {
            $bottleType = $bottleTypes->random();

            $product = Product::factory()
                ->bottle($bottleType->id, $center->id, [
                    'is_filled' => false, // These bottles are always empty
                    'status' => BottleStatus::RETURNED_TO_SUPPLIER(),
                ])
                ->create();

            // First, create the stock entry (initial state)
            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                'notes' => 'Bottle received from supplier and put in stock',
                'created_at' => now()->subMonths(rand(6, 12)),
            ]);

            // Then, record the return to supplier (final state)
            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::GAS_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::RETURN_TO_SUPPLIER(),
                'notes' => fake()->randomElement([
                    'Defective bottle returned to supplier',
                    'Bottle with damaged valve',
                    'Returned due to corrosion',
                    'Too old bottle, returned for renewal',
                ]),
                'created_at' => now()->subMonths(rand(1, 3)),
            ]);
        }

        $this->command->info("{$count} returned-to-supplier bottles created for {$center->name}");
    }

    /**
     * Update the pivot table stock counts to match the actual bottle counts
     */
    private function updateStockCounts(DistributionCenter $center): void
    {
        $this->command->info("Updating stock counts for {$center->name}...");

        $bottleTypes = BottleType::all();

        foreach ($bottleTypes as $bottleType) {
            // Find the product category for this bottle type
            $productCategory = ProductCategory::where('product_type', ProductType::BOTTLE())
                ->where('product_type_id', $bottleType->id)
                ->first();

            if (! $productCategory) {
                continue;
            }

            // Get current values in pivot table
            $pivotValues = $this->getPivotStockValues($center->id, $bottleType->id);
            $oldEmptyCount = $pivotValues['empty'];
            $oldFilledCount = $pivotValues['filled'];

            // Get current bottle values
            $actualValues = $this->getActualStockValues($center->id, $bottleType->id);
            $emptyCount = $actualValues['empty'];
            $filledCount = $actualValues['filled'];

            // Update the pivot table
            DB::table('product_category_distribution_center')
                ->where('distribution_center_id', $center->id)
                ->where('product_category_id', $productCategory->id)
                ->update([
                    'stock_empty' => $emptyCount,
                    'stock_filled' => $filledCount,
                    'updated_at' => now(),
                ]);
        }
    }
}
