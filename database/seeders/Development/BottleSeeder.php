<?php

// database/seeders/Development/BottleSeeder.php

namespace Database\Seeders\Development;

use App\Enums\BottleMovementType;
use App\Enums\BottleStatus;
use App\Enums\UserRole;
use App\Models\Bottle;
use App\Models\BottleMovement;
use App\Models\BottleType;
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
            // Seul BottleSeeder crée des bouteilles IN_STOCK, LOST_STOLEN, et RETURNED_TO_SUPPLIER
            $this->createBottlesInStock($center, $bottleTypes);
            $this->createLostOrStolenBottles($center, $bottleTypes);
            $this->createReturnedToSupplierBottles($center, $bottleTypes);

            // Update the pivot table to reflect the actual bottle counts
            $this->updateStockCounts($center);
        }

        $this->command->info('Development bottles created successfully!');
    }

    /**
     * Récupère et affiche les statistiques détaillées des stocks pour un centre
     */
    private function displayStockStatistics(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->line('--------------------------------------------------');
        $this->command->line("STATISTIQUES DE STOCK POUR: {$center->name}");
        $this->command->line('--------------------------------------------------');

        $totalPivotEmpty = 0;
        $totalPivotFilled = 0;
        $totalActualEmpty = 0;
        $totalActualFilled = 0;

        foreach ($bottleTypes as $bottleType) {
            // Récupérer les valeurs du pivot
            $pivotValues = $this->getPivotStockValues($center->id, $bottleType->id);
            $pivotEmpty = $pivotValues['empty'];
            $pivotFilled = $pivotValues['filled'];

            // Récupérer les valeurs réelles (calculées à partir de la table des bouteilles)
            $actualValues = $this->getActualStockValues($center->id, $bottleType->id);
            $actualEmpty = $actualValues['empty'];
            $actualFilled = $actualValues['filled'];

            // Calculer les différences
            $emptyDiff = $actualEmpty - $pivotEmpty;
            $filledDiff = $actualFilled - $pivotFilled;

            // Formater les différences
            $emptyDiffFormatted = $this->formatDifference($emptyDiff);
            $filledDiffFormatted = $this->formatDifference($filledDiff);

            // Mettre à jour les totaux
            $totalPivotEmpty += $pivotEmpty;
            $totalPivotFilled += $pivotFilled;
            $totalActualEmpty += $actualEmpty;
            $totalActualFilled += $actualFilled;

            // Afficher les statistiques pour ce type de bouteille
            $this->command->line("{$bottleType->name}:");
            $this->command->line("  - PIVOT   : {$pivotEmpty} vides, {$pivotFilled} pleines");
            $this->command->line("  - BOTTLES : {$actualEmpty} vides {$emptyDiffFormatted}, {$actualFilled} pleines {$filledDiffFormatted}");

            if ($emptyDiff !== 0 || $filledDiff !== 0) {
                $this->command->warn("  ⚠️ DIFFÉRENCE DÉTECTÉE pour {$bottleType->name} dans {$center->name}");
            }
        }

        // Afficher les totaux
        $totalEmptyDiff = $totalActualEmpty - $totalPivotEmpty;
        $totalFilledDiff = $totalActualFilled - $totalPivotFilled;
        $totalEmptyDiffFormatted = $this->formatDifference($totalEmptyDiff);
        $totalFilledDiffFormatted = $this->formatDifference($totalFilledDiff);

        $this->command->line('--------------------------------------------------');
        $this->command->line('TOTAUX:');
        $this->command->line("  - PIVOT   : {$totalPivotEmpty} vides, {$totalPivotFilled} pleines, Total: ".($totalPivotEmpty + $totalPivotFilled));
        $this->command->line("  - BOTTLES : {$totalActualEmpty} vides {$totalEmptyDiffFormatted}, {$totalActualFilled} pleines {$totalFilledDiffFormatted}, Total: ".($totalActualEmpty + $totalActualFilled).' '.$this->formatDifference($totalEmptyDiff + $totalFilledDiff));

        if ($totalEmptyDiff !== 0 || $totalFilledDiff !== 0) {
            $this->command->warn("  ⚠️ DIFFÉRENCE TOTALE DÉTECTÉE pour {$center->name}");
        }

        $this->command->line('--------------------------------------------------');
    }

    /**
     * Récupère les valeurs de stock depuis la table pivot
     */
    private function getPivotStockValues(int $centerId, int $bottleTypeId): array
    {
        $stockData = DB::table('bottle_type_distribution_center')
            ->where('distribution_center_id', $centerId)
            ->where('bottle_type_id', $bottleTypeId)
            ->first();

        return [
            'empty' => $stockData ? (int) $stockData->stock_empty : 0,
            'filled' => $stockData ? (int) $stockData->stock_filled : 0,
        ];
    }

    /**
     * Récupère les valeurs de stock actuelles en comptant les bouteilles
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
     * Formate une différence pour l'affichage
     */
    private function formatDifference(int $diff): string
    {
        if ($diff === 0) {
            return '(identique)';
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

        // Compteurs pour les logs
        $totalEmptyCount = 0;
        $totalFilledCount = 0;
        $bottleTypeCounts = [];

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

                // Ajout d'un mouvement pour l'entrée en stock
                BottleMovement::create([
                    'bottle_id' => $product->bottle->id,
                    'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                    'distribution_center_id' => $center->id,
                    'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                    'notes' => 'Bouteille vide reçue du fournisseur et mise en stock',
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

                // Ajout d'un mouvement pour l'entrée en stock
                BottleMovement::create([
                    'bottle_id' => $product->bottle->id,
                    'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                    'distribution_center_id' => $center->id,
                    'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                    'notes' => 'Bouteille pleine reçue du fournisseur et mise en stock',
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

            // D'abord, on crée l'entrée en stock (état initial)
            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                'notes' => 'Bouteille reçue du fournisseur et mise en stock',
                'created_at' => now()->subMonths(rand(6, 12)),
            ]);

            // Ensuite, on enregistre la perte/vol (état final)
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
                    'is_filled' => false, // Ces bouteilles sont toujours vides
                    'status' => BottleStatus::RETURNED_TO_SUPPLIER(),
                ])
                ->create();

            // D'abord, on crée l'entrée en stock (état initial)
            BottleMovement::create([
                'bottle_id' => $product->bottle->id,
                'user_id' => User::role([UserRole::MANAGER()->value, UserRole::CENTER_MANAGER()->value])->inRandomOrder()->first()->id ?? 1,
                'distribution_center_id' => $center->id,
                'type' => BottleMovementType::SUPPLIER_DELIVERY(),
                'notes' => 'Bouteille reçue du fournisseur et mise en stock',
                'created_at' => now()->subMonths(rand(6, 12)),
            ]);

            // Ensuite, on enregistre le retour au fournisseur (état final)
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
            // Récupérer les valeurs actuelles dans la table pivot
            $pivotValues = $this->getPivotStockValues($center->id, $bottleType->id);
            $oldEmptyCount = $pivotValues['empty'];
            $oldFilledCount = $pivotValues['filled'];

            // Récupérer les valeurs actuelles des bouteilles
            $actualValues = $this->getActualStockValues($center->id, $bottleType->id);
            $emptyCount = $actualValues['empty'];
            $filledCount = $actualValues['filled'];

            // Update the pivot table
            DB::table('bottle_type_distribution_center')
                ->where('distribution_center_id', $center->id)
                ->where('bottle_type_id', $bottleType->id)
                ->update([
                    'stock_empty' => $emptyCount,
                    'stock_filled' => $filledCount,
                    'updated_at' => now(),
                ]);
        }
    }
}
