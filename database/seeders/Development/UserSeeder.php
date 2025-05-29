<?php

// database/seeders/Development/UserSeeder.php

namespace Database\Seeders\Development;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating development users...');

        $this->createAdminUsers();
        $this->createManagerUsers();
        $this->createAccountantUsers();
        $this->createGasManagers();
        $this->createCenterManagers();
        $this->createDeliveryPersons();
        $this->createCustomers();

        $this->command->info('Development users created successfully!');
    }

    /**
     * Create admin users
     */
    private function createAdminUsers(): void
    {
        User::factory()
            ->count(2)
            ->create()
            ->each(function ($user) {
                $user->assignRole(UserRole::ADMIN()->value);
                $user->email = 'admin'.$user->id.'@example.com';
                $user->save();
            });

        $this->command->info('2 admin users created.');
    }

    /**
     * Create manager users
     */
    private function createManagerUsers(): void
    {
        User::factory()
            ->manager()
            ->count(2)
            ->create()
            ->each(function ($user) {
                $user->assignRole(UserRole::MANAGER()->value);
            });

        $this->command->info('2 manager users created.');
    }

    /**
     * Create accountant users
     */
    private function createAccountantUsers(): void
    {
        User::factory()
            ->accountant()
            ->count(3)
            ->create()
            ->each(function ($user) {
                $user->assignRole(UserRole::ACCOUNTANT()->value);
            });

        $this->command->info('3 accountant users created.');
    }

    /**
     * Create gas manager users
     */
    private function createGasManagers(): void
    {
        User::factory()
            ->count(2)
            ->create()
            ->each(function ($user) {
                $user->assignRole(UserRole::GAS_MANAGER()->value);
            });

        $this->command->info('2 gas manager users created.');
    }

    /**
     * Create center manager users
     */
    private function createCenterManagers(): void
    {
        $centers = DistributionCenter::all();

        if ($centers->count() === 0) {
            $this->command->warn('No distribution centers found. Center managers not created.');

            return;
        }

        foreach ($centers as $center) {
            $user = User::factory()
                ->centerManager()
                ->create();

            $user->assignRole(UserRole::CENTER_MANAGER()->value);
            $user->distributionCenters()->create([
                'distribution_center_id' => $center->id,
            ]);
        }

        $this->command->info($centers->count().' center manager users created.');
    }

    /**
     * Create delivery person users
     */
    private function createDeliveryPersons(): void
    {
        $centers = DistributionCenter::all();

        if ($centers->count() === 0) {
            $this->command->warn('No distribution centers found. Delivery persons not created.');

            return;
        }

        foreach ($centers as $center) {
            User::factory()
                ->deliveryPerson($center->id, true)
                ->count(4)
                ->create();

            User::factory()
                ->deliveryPerson($center->id, false)
                ->count(1)
                ->create();
        }

        $this->command->info($centers->count() * 5 .' delivery persons created (4 active + 1 inactive per center).');
    }

    /**
     * Create customer users
     */
    private function createCustomers(): void
    {
        User::factory()
            ->customer()
            ->count(20)
            ->create();
        User::factory()
            ->customer(addressesCount: 2)
            ->count(10)
            ->create();

        User::factory()
            ->customer(1000.0)
            ->count(10)
            ->create();

        User::factory()
            ->customer(0.0)
            ->count(10)
            ->create();

        $this->command->info('50 customers created (30 random + 10 VIP + 10 new).');
    }
}
