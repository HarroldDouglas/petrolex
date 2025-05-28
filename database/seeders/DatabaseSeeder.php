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
        $this->call([
            RolePermissionSeeder::class,
            Production\BottleTypeSeeder::class,
        ]);

        if (app()->environment('local', 'development', 'testing')) {
            $this->call(DevelopmentSeeder::class);
            $this->command->info('Development data seeded successfully!');
        }
    }
}
