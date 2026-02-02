<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core seeders (required for all environments)
        $this->call([
            GeographicSeeder::class,
            RolePermissionSeeder::class,
            UserSeeder::class,
            TestCustomerSeeder::class, // Permanent test customer for mobile app testing
            Production\BottleTypeSeeder::class,
            Production\AccessoryTypeSeeder::class,
        ]);

        if (app()->environment('local', 'development', 'testing')) {
            // Development seeders (includes DistributionCenters needed for products)
            $this->call(DevelopmentSeeder::class);
            $this->command->info('Development data seeded successfully!');
        }

        // Product seeders (need DistributionCenters to exist first)
        $this->call([
            Production\ProductCategorySeeder::class,
            Production\ProductSeeder::class,
        ]);
    }
}
