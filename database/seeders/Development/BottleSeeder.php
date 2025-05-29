<?php

// database/seeders/Development/BottleSeeder.php

namespace Database\Seeders\Development;

use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Enums\UserRole;
use App\Models\Bottle;
use App\Models\BottleMovement;
use App\Models\BottleType;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Product;
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
        $pivotExists = DB::table('bottle_type_distribution_center')->count() > 0;
        if (! $pivotExists) {
            $this->command->error('Stock data not found. Run BottleTypeDistributionCenterSeeder first.');

            return;
        }

        foreach ($centers as $center) {
            $this->createBottlesInStock($center, $bottleTypes);
            $this->createBottlesWithDeliveryPersons($center);
            $this->createBottlesWithClients($center);
            $this->createLostOrStolenBottles($center, $bottleTypes);
            $this->createReturnedToSupplierBottles($center, $bottleTypes);

            // Update the pivot table to reflect the actual bottle counts
            $this->updateStockCounts($center);
        }

        $this->command->info('Development bottles created successfully!');
    }

    /**
     * Create bottles in stock for a distribution center based on pivot table data
     */
    private function createBottlesInStock(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->info("Creating bottles in stock for {$center->name}...");

        foreach ($bottleTypes as $bottleType) {
            // Get the stock counts from the pivot table
            $stockData = DB::table('bottle_type_distribution_center')
                ->where('distribution_center_id', $center->id)
                ->where('bottle_type_id', $bottleType->id)
                ->first();

            if (! $stockData) {
                $this->command->warn("No stock data found for {$bottleType->name} in {$center->name}. Skipping.");
                continue;
            }

            $emptyCount = min($stockData->stock_empty, 40); // Limit to prevent too many records
            $filledCount = min($stockData->stock_filled, 40); // Limit to prevent too many records

            // Create empty bottles
            for ($i = 0; $i < $emptyCount; $i++) {
                Product::factory()
                    ->bottle($bottleType->id, $center->id, [
                        'is_filled' => false,
                        'status' => BottleStatus::IN_STOCK(),
                    ])
                    ->create();
            }

            // Create filled bottles
            for ($i = 0; $i < $filledCount; $i++) {
                Product::factory()
                    ->bottle($bottleType->id, $center->id, [
                        'is_filled' => true,
                        'status' => BottleStatus::IN_STOCK(),
                    ])
                    ->create();
            }

            $this->command->info("{$emptyCount} empty and {$filledCount} filled {$bottleType->name} bottles created for {$center->name}");
        }
    }

    /**
     * Create bottles assigned to delivery persons
     */
    private function createBottlesWithDeliveryPersons(DistributionCenter $center): void
    {
        $this->command->info("Creating bottles with delivery persons for {$center->name}...");

        $deliveryPersons = DeliveryPerson::whereHas('distributionCenters', function ($query) use ($center) {
            $query->where('distribution_center_id', $center->id);
        })->get()->map(function ($dp) {
            return $dp->user;
        });

        if ($deliveryPersons->isEmpty()) {
            $this->command->warn("No delivery persons found for {$center->name}. Skipping bottle assignment.");

            return;
        }

        $bottleTypes = BottleType::all();

        foreach ($deliveryPersons as $deliveryPerson) {
            // Each delivery person has between 2 and 8 bottles assigned
            $bottleCount = fake()->numberBetween(2, 8);

            for ($i = 0; $i < $bottleCount; $i++) {
                $bottleType = $bottleTypes->random();

                // Create the bottle (always filled as it is in delivery)
                $product = Product::factory()
                    ->bottle($bottleType->id, $center->id, [
                        'is_filled' => true,
                        'status' => BottleStatus::WITH_DELIVERY_PERSON(),
                    ])
                    ->create();

                // Record the movement of this bottle
                BottleMovement::create([
                    'bottle_id' => $product->bottle->id,
                    'user_id' => $deliveryPerson->id,
                    'distribution_center_id' => $center->id,
                    'type' => BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
                    'notes' => 'Bouteille assignée au livreur pour distribution',
                    'created_at' => now()->subHours(rand(1, 24)),
                ]);
            }

            $this->command->info("{$bottleCount} bottles assigned to delivery person {$deliveryPerson->name}");
        }
    }

    /**
     * Create bottles with clients
     */
    private function createBottlesWithClients(DistributionCenter $center): void
    {
        $this->command->info("Creating bottles with clients for {$center->name}...");

        $customers = User::role(UserRole::CUSTOMER()->value)->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Skipping bottle assignment to clients.');

            return;
        }

        $bottleTypes = BottleType::all();
        $deliveryPersons = User::role(UserRole::DELIVERY_PERSON()->value)->get();

        foreach ($customers as $customer) {
            $bottleCount = fake()->numberBetween(0, 3);

            if ($bottleCount === 0) {
                continue;
            }

            for ($i = 0; $i < $bottleCount; $i++) {
                $bottleType = $bottleTypes->random();

                $product = Product::factory()
                    ->bottle($bottleType->id, $center->id, [
                        'is_filled' => true,
                        'status' => BottleStatus::WITH_CLIENT(),
                    ])
                    ->create();

                if ($deliveryPersons->isNotEmpty()) {
                    $deliveryPerson = $deliveryPersons->random();

                    BottleMovement::create([
                        'bottle_id' => $product->bottle->id,
                        'user_id' => $deliveryPerson->id,
                        'distribution_center_id' => $center->id,
                        'type' => BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
                        'notes' => 'Bouteille assignée au livreur',
                        'created_at' => now()->subDays(rand(1, 30))->subHours(rand(1, 12)),
                    ]);

                    BottleMovement::create([
                        'bottle_id' => $product->bottle->id,
                        'user_id' => $customer->id,
                        'distribution_center_id' => $center->id,
                        'type' => BottleMovementType::DELIVERY_TO_CUSTOMER(),
                        'notes' => 'Bouteille livrée au client',
                        'created_at' => now()->subDays(rand(1, 30)),
                    ]);
                }
            }

            if ($bottleCount > 0) {
                $this->command->info("{$bottleCount} bottles assigned to customer {$customer->name}");
            }
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

            $product = Product::factory()
                ->bottle($bottleType->id, $center->id, [
                    'is_filled' => fake()->boolean(),
                    'status' => BottleStatus::LOST_STOLEN(),
                ])
                ->create();

            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::DECLARE_LOST_STOLEN(),
                'notes' => fake()->randomElement([
                    'Bouteille déclarée perdue lors du dernier inventaire',
                    'Bouteille non retrouvée après livraison',
                    'Signalée comme volée par le client',
                    'Disparition constatée au dépôt',
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
                    'is_filled' => false,
                    'status' => BottleStatus::RETURNED_TO_SUPPLIER(),
                ])
                ->create();

            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::GAS_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::RETURN_TO_SUPPLIER(),
                'notes' => fake()->randomElement([
                    'Bouteille défectueuse retournée au fournisseur',
                    'Bouteille avec valve endommagée',
                    'Retournée pour cause de corrosion',
                    'Bouteille trop ancienne, retournée pour renouvellement',
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
            // Count actual bottles in stock (not with delivery persons, clients, etc)
            $emptyCount = \App\Models\Bottle::where('bottle_type_id', $bottleType->id)
                ->where('is_filled', false)
                ->where('status', BottleStatus::IN_STOCK())
                ->where('distribution_center_id', $center->id)
                ->count();

            $filledCount = \App\Models\Bottle::where('bottle_type_id', $bottleType->id)
                ->where('is_filled', true)
                ->where('status', BottleStatus::IN_STOCK())
                ->where('distribution_center_id', $center->id)
                ->count();

            // Update the pivot table
            DB::table('bottle_type_distribution_center')
                ->where('distribution_center_id', $center->id)
                ->where('bottle_type_id', $bottleType->id)
                ->update([
                    'stock_empty' => $emptyCount,
                    'stock_filled' => $filledCount,
                    'updated_at' => now(),
                ]);

            $this->command->info("Updated {$bottleType->name} stock in {$center->name}: {$emptyCount} empty, {$filledCount} filled");
        }
    }
}
