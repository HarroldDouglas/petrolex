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
            TestDeliveryPersonSeeder::class, // Permanent test delivery person for mobile app testing
            Production\BottleTypeSeeder::class,
            Production\AccessoryTypeSeeder::class,
        ]);

        if (app()->environment('local', 'development', 'testing')) {
            // Development seeders (includes DistributionCenters needed for products)
            $this->call(DevelopmentSeeder::class);
            $this->command->info('Development data seeded successfully!');
        }

        // Product category seeders (reference data)
        $this->call([
            Production\ProductCategorySeeder::class,
            Production\DistributionCenterSeeder::class,
            AppVersionSeeder::class,
        ]);

        // ProductSeeder only in dev (production uses real data created via admin)
        if (app()->environment('local', 'development', 'testing')) {
            $this->call(Production\ProductSeeder::class);
        }
    }
}
