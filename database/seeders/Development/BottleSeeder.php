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
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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

        // Créer des bouteilles avec différents statuts pour chaque centre
        foreach ($centers as $center) {
            $this->createBottlesInStock($center, $bottleTypes);
            $this->createBottlesWithDeliveryPersons($center);
            $this->createBottlesWithClients($center);
            $this->createLostOrStolenBottles($center, $bottleTypes);
            $this->createReturnedToSupplierBottles($center, $bottleTypes);
        }

        $this->command->info('Development bottles created successfully!');
    }

    /**
     * Create bottles in stock for a distribution center
     */
    private function createBottlesInStock(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->info("Creating bottles in stock for {$center->name}...");

        foreach ($bottleTypes as $bottleType) {
            $count = fake()->numberBetween(15, 40);

            for ($i = 0; $i < $count; $i++) {
                // 65% bottles are filled, 35% are empty
                $isFilled = fake()->boolean(65);

                Bottle::create([
                    'product_id' => $bottleType->id,
                    'bottle_type_id' => $bottleType->id,
                    'distribution_center_id' => $center->id,
                    'barcode' => 'BT'.strtoupper(Str::random(8)),
                    'is_filled' => $isFilled,
                    'status' => BottleStatus::IN_STOCK(),
                ]);
            }

            $this->command->info("{$count} {$bottleType->name} bottles created for {$center->name}");
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
                $bottle = Bottle::create([
                    'product_id' => $bottleType->id,
                    'bottle_type_id' => $bottleType->id,
                    'distribution_center_id' => $center->id,
                    'barcode' => 'BT'.strtoupper(Str::random(8)),
                    'is_filled' => true,
                    'status' => BottleStatus::WITH_DELIVERY_PERSON(),
                ]);

                // Record the movement of this bottle
                BottleMovement::create([
                    'bottle_id' => $bottle->id,
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
            // Chaque client a entre 0 et 3 bouteilles
            $bottleCount = fake()->numberBetween(0, 3);

            if ($bottleCount === 0) {
                continue;
            }

            for ($i = 0; $i < $bottleCount; $i++) {
                $bottleType = $bottleTypes->random();

                $bottle = Bottle::create([
                    'product_id' => $bottleType->id, // Association avec le produit correspondant
                    'bottle_type_id' => $bottleType->id,
                    'distribution_center_id' => $center->id,
                    'barcode' => 'BT'.strtoupper(Str::random(8)),
                    'is_filled' => true,
                    'status' => BottleStatus::WITH_CLIENT(),
                ]);

                if ($deliveryPersons->isNotEmpty()) {
                    $deliveryPerson = $deliveryPersons->random();

                    BottleMovement::create([
                        'bottle_id' => $bottle->id,
                        'user_id' => $deliveryPerson->id,
                        'distribution_center_id' => $center->id,
                        'type' => BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
                        'notes' => 'Bouteille assignée au livreur',
                        'created_at' => now()->subDays(rand(1, 30))->subHours(rand(1, 12)),
                    ]);

                    BottleMovement::create([
                        'bottle_id' => $bottle->id,
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

            $bottle = Bottle::create([
                'product_id' => $bottleType->id,
                'bottle_type_id' => $bottleType->id,
                'distribution_center_id' => $center->id,
                'barcode' => 'BT'.strtoupper(Str::random(8)),
                'is_filled' => fake()->boolean(), // Can be empty or full
                'status' => BottleStatus::LOST_STOLEN(),
            ]);

            BottleMovement::create([
                'bottle_id' => $bottle->id,
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

            $bottle = Bottle::create([
                'product_id' => $bottleType->id,
                'bottle_type_id' => $bottleType->id,
                'distribution_center_id' => $center->id,
                'barcode' => 'BT'.strtoupper(Str::random(8)),
                'is_filled' => false,
                'status' => BottleStatus::RETURNED_TO_SUPPLIER(),
            ]);

            BottleMovement::create([
                'bottle_id' => $bottle->id,
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
}
