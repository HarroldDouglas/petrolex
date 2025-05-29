<?php

// database/seeders/Development/BottleSeeder.php

namespace Database\Seeders\Development;

use App\Models\BottleType;
use App\Models\DistributionCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BottleTypeDistributionCenterSeeder extends Seeder
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

        // Clear any existing pivot data to avoid duplicates
        DB::table('bottle_type_distribution_center')->truncate();

        foreach ($centers as $center) {
            $this->command->info("Setting up bottle stocks for {$center->name}...");

            foreach ($bottleTypes as $bottleType) {
                // Generate random stock quantities
                $stockEmpty = rand(15, 20);
                $stockFilled = rand(25, 60);

                // Insert into pivot table
                DB::table('bottle_type_distribution_center')->insert([
                    'distribution_center_id' => $center->id,
                    'bottle_type_id' => $bottleType->id,
                    'stock_empty' => $stockEmpty,
                    'stock_filled' => $stockFilled,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("  - {$bottleType->name}: {$stockEmpty} empty, {$stockFilled} filled");
            }
        }

        $this->command->info('Bottle type stocks for distribution centers created successfully!');
    }
}
