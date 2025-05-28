<?php

// database/seeders/ProductionSeeder.php

namespace Database\Seeders;

use Database\Seeders\Production\BottleTypeSeeder;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed the application's database with production data.
     */
    public function run(): void
    {
        $this->command->info('========================================');
        $this->command->info('Starting Production Database Seeding...');
        $this->command->info('========================================');

        $this->call([
            RolePermissionSeeder::class,
            BottleTypeSeeder::class,
        ]);

        $this->command->info('========================================');
        $this->command->info('Production Data Seeded Successfully!');
        $this->command->info('========================================');
    }
}
