<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
    /**
     * Seed the application's database with development data.
     */
    public function run(): void
    {
        $this->command->info('===========================================');
        $this->command->info('Starting Development Database Seeding...');
        $this->command->info('===========================================');

        $this->call(Development\DistributionCenterSeeder::class);
        $this->call(Development\UserSeeder::class);
        $this->call(Production\AccessoryTypeSeeder::class);
        // BottleTypeSeeder is already called in main DatabaseSeeder
        $this->call(Production\ProductCategorySeeder::class);
        $this->call(Production\ProductSeeder::class);
        $this->call(Development\BottleSeeder::class);
        $this->call(Development\AccessorySeeder::class);
        $this->call(Development\OrderSeeder::class);
        $this->call(Development\SupplierDeliverySeeder::class);
        // $this->call(Development\DemoDataSeeder::class);

        $this->command->info('===========================================');
        $this->command->info('Development Data Seeded Successfully!');
        $this->command->info('===========================================');
    }
}
