<?php

// database/seeders/Development/UserSeeder.php

namespace Database\Seeders\Development;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\User;
use App\Models\UserDistributionCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
        $this->assignAllDistributionCentersToAdmin();

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
                'is_active' => true,
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

        User::factory()
            ->deliveryPerson($centers->first()->id, true)
            ->create(['email' => 'delivery1@test.com']);

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

        $this->command->info($centers->count() * 5 + 1 .' delivery persons created (4 active + 1 inactive per center, +1 specific).');
    }

    /**
     * Create customer users
     */
    private function createCustomers(): void
    {
        $this->createTestCustomer();

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

        $this->command->info('51 customers created (1 specific + 30 random + 10 VIP + 10 new).');
    }

    /**
     * Create a specific customer for testing purposes
     */
    private function createTestCustomer(): void
    {
        // Create user without addresses first
        $customerUser = User::factory()
            ->state([
                'first_name' => 'Customer',
                'last_name' => 'Test',
                'email' => 'customer1@test.com',
            ])
            ->create();

        // Manually create customer without using factory's afterCreating
        $customerUser->assignRole(UserRole::CUSTOMER()->value);
        $customer = Customer::create([
            'user_id' => $customerUser->id,
            'current_balance' => 0.0,
        ]);

        // Use Melen neighborhood in Yaoundé I (should be the first one created)
        $neighborhood = \App\Models\Geography\Neighborhood::where('name', 'Melen')
            ->whereHas('municipality', function ($q) {
                $q->where('name', 'Yaoundé I');
            })
            ->first();

        if (! $neighborhood) {
            $this->command->error('Melen neighborhood not found. Please seed geographic data first.');

            return;
        }

        // Create first address as default
        $customer->deliveryAddresses()->create([
            'label' => 'Nkoabang',
            'address' => 'Nkoabang',
            'latitude' => 3.8617882,
            'longitude' => 11.5835694,
            'phone' => '+237677889900',
            'phone_country_code' => '+237',
            'contact_firstname' => 'Marie',
            'contact_lastname' => 'Dupont',
            'email' => 'marie.dupont@example.com',
            'address_precision' => 'Près du marché central',
            'is_default' => true,
            'neighborhood_id' => $neighborhood->id,
        ]);

        $customer->deliveryAddresses()->create([
            'label' => 'Poste Centrale',
            'address' => 'Poste Centrale',
            'latitude' => 3.8741355,
            'longitude' => 11.5173166,
            'phone' => '+237688776655',
            'phone_country_code' => '+237',
            'contact_firstname' => 'Jean',
            'contact_lastname' => 'Martin',
            'email' => 'jean.martin@example.com',
            'address_precision' => 'Face à la poste principale',
            'neighborhood_id' => $neighborhood->id,
        ]);

        $customer->deliveryAddresses()->create([
            'label' => 'Essos',
            'address' => 'Essos',
            'latitude' => 3.868779,
            'longitude' => 11.542277,
            'phone' => '+237699554433',
            'phone_country_code' => '+237',
            'contact_firstname' => 'Paul',
            'contact_lastname' => 'Nguema',
            'email' => 'paul.nguema@example.com',
            'address_precision' => 'Quartier Essos, près de l\'école',
            'neighborhood_id' => $neighborhood->id,
        ]);
    }

    /**
     * Assign all distribution centers to the admin user defined in the .env file
     */
    private function assignAllDistributionCentersToAdmin(): void
    {
        $adminEmail = env('ADMIN_EMAIL');

        if (! $adminEmail) {
            $this->command->warn('ADMIN_EMAIL not defined in .env file. Skipping distribution centers assignment.');

            return;
        }

        $adminUser = User::where('email', $adminEmail)->first();

        if (! $adminUser) {
            $this->command->warn("User with email {$adminEmail} not found. Skipping distribution centers assignment.");

            return;
        }

        $distributionCenters = DistributionCenter::all();

        DB::transaction(function () use ($adminUser, $distributionCenters) {
            UserDistributionCenter::where('user_id', $adminUser->id)->forceDelete();

            $records = $distributionCenters->map(function ($center) use ($adminUser) {
                return [
                    'user_id' => $adminUser->id,
                    'distribution_center_id' => $center->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            if (! empty($records)) {
                UserDistributionCenter::insert($records);
            }
        });

        $this->command->info("Successfully assigned {$distributionCenters->count()} distribution centers to admin ({$adminEmail})");
    }
}
