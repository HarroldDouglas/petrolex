<?php

// database/seeders/Development/DemoDataSeeder.php

namespace Database\Seeders\Development;

use App\Enums\BottleStatus;
use App\Enums\UserRole;
use App\Models\Bottle;
use App\Models\Customer;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds for demonstration environments.
     */
    public function run(): void
    {
        $this->command->info('Creating demo data for staging/demo environments...');

        $this->createDemoDistributionCenters();
        $this->createDemoUsers();
        $this->createDemoCustomerData();
        $this->createDemoBottlesAndOrders();

        $this->command->info('Demo data created successfully!');
    }

    /**
     * Create demo distribution centers
     */
    private function createDemoDistributionCenters(): void
    {
        DistributionCenter::firstOrCreate(
            ['name' => 'Demo Distribution Center'],
            [
                'address' => '123 Main Street, Demo City',
                'latitude' => 4.0511,
                'longitude' => 9.7679,
                'phone' => '123456789',
                'email' => 'demo.center@example.com',
                'is_active' => true,
            ]
        );

        $this->command->info('Demo distribution centers created.');
    }

    /**
     * Create demo users with predictable credentials
     */
    private function createDemoUsers(): void
    {
        $demoCenter = DistributionCenter::where('name', 'Demo Distribution Center')->first();

        // Create a demo manager
        User::firstOrCreate(
            ['email' => 'demo.manager@example.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Manager',
                'phone_number' => '1234567890',
                'address' => '123 Demo Street, Demo City',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        )->assignRole(UserRole::MANAGER()->value);

        // Create a demo center manager
        $centerManagerUser = User::firstOrCreate(
            ['email' => 'demo.center@example.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Center',
                'phone_number' => '2345678901',
                'address' => '456 Demo Avenue, Demo City',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $centerManagerUser->assignRole(UserRole::CENTER_MANAGER()->value);

        // Create a demo delivery person
        $deliveryPersonUser = User::firstOrCreate(
            ['email' => 'demo.delivery@example.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Delivery',
                'phone_number' => '3456789012',
                'address' => '789 Demo Road, Demo City',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $deliveryPersonUser->assignRole(UserRole::DELIVERY_PERSON()->value);

        // Create delivery person record
        $deliveryPerson = DeliveryPerson::firstOrCreate(
            ['user_id' => $deliveryPersonUser->id],
            ['is_active' => true]
        );

        // Assign delivery person to distribution center
        if ($demoCenter) {
            $deliveryPerson->distributionCenters()->sync([$demoCenter->id]);
        }

        // Create a demo customer user
        $demoCustomerUser = User::firstOrCreate(
            ['email' => 'demo.customer@example.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Customer',
                'phone_number' => '4567890123',
                'address' => '321 Demo Boulevard, Demo City',
                'password' => Hash::make('demo123'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $demoCustomerUser->assignRole(UserRole::CUSTOMER()->value);

        // Ensure the customer record exists
        Customer::firstOrCreate(
            ['user_id' => $demoCustomerUser->id],
            ['current_balance' => 0.00]
        );

        $this->command->info('Demo users created with password "demo123".');
    }

    /**
     * Create demo customer data (addresses)
     */
    private function createDemoCustomerData(): void
    {
        // Récupérer directement le customer démo
        $customerRecord = Customer::whereHas('user', function ($query) {
            $query->where('email', 'demo.customer@example.com');
        })->first();

        if (! $customerRecord) {
            $this->command->error('Demo customer not found. Create it first.');

            return;
        }

        $demoCustomerUser = $customerRecord->user;

        // Create a delivery address for the demo customer
        \App\Models\CustomerDeliveryAddress::firstOrCreate(
            [
                'customer_id' => $customerRecord->id,
                'label' => 'Home',
            ],
            [
                'address' => '321 Demo Boulevard, Demo City',
                'latitude' => fake()->latitude(),
                'longitude' => fake()->longitude(),
                'phone' => $demoCustomerUser->phone_number,
                'contact_name' => $demoCustomerUser->first_name.' '.$demoCustomerUser->last_name,
                'is_default' => true,
            ]
        );

        $this->command->info('Demo customer delivery address created.');
    }

    /**
     * Create demo bottles and orders
     */
    private function createDemoBottlesAndOrders(): void
    {
        $deliveryPerson = DeliveryPerson::whereHas('user', function ($query) {
            $query->where('email', 'demo.delivery@example.com');
        })->first();

        $customerRecord = Customer::whereHas('user', function ($query) {
            $query->where('email', 'demo.customer@example.com');
        })->first();

        if (! $customerRecord || ! $deliveryPerson) {
            $this->command->error('Demo users not found. Create them first.');

            return;
        }

        $demoCustomerUser = $customerRecord->user;
        $demoDeliveryPersonUser = $deliveryPerson->user;

        $this->createDemoBottles($deliveryPerson, $demoCustomerUser);
        $this->createDemoOrders($customerRecord, $deliveryPerson);
    }

    /**
     * Create demo bottles
     */
    private function createDemoBottles($deliveryPerson, $demoCustomerUser): void
    {
        // Create bottles for the demo delivery person
        $deliveryPersonBottles = Bottle::factory()
            ->count(5)
            ->withDeliveryPerson()
            ->create(['status' => BottleStatus::WITH_DELIVERY_PERSON()]);

        // Assign bottles to demo delivery person
        foreach ($deliveryPersonBottles as $bottle) {
            if (method_exists($bottle, 'assignToDeliveryPerson')) {
                $bottle->assignToDeliveryPerson($deliveryPerson->user_id);
            }
        }

        // Create bottles for the demo customer
        $customerBottles = Bottle::factory()
            ->count(2)
            ->withClient()
            ->create(['status' => BottleStatus::WITH_CLIENT()]);

        // Assign bottles to demo customer
        foreach ($customerBottles as $bottle) {
            if (method_exists($bottle, 'assignToCustomer')) {
                $bottle->assignToCustomer($demoCustomerUser->id);
            }
        }

        $this->command->info('Demo bottles created and assigned.');
    }

    /**
     * Create demo orders
     */
    private function createDemoOrders($customerRecord, $deliveryPerson): void
    {
        $defaultAddress = $customerRecord->deliveryAddresses()->where('is_default', true)->first();

        if (! $defaultAddress) {
            $this->command->error('Demo customer has no default address. Creating orders with null address.');
        }

        // Create confirmed order
        Order::factory()
            ->confirmed()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-CONF-001',
                'delivery_address_id' => $defaultAddress?->id,
            ]);

        // Create processing order
        Order::factory()
            ->processing()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-PROC-001',
                'delivery_address_id' => $defaultAddress?->id,
                'delivery_person_id' => $deliveryPerson->id,
            ]);

        // Create delivered order
        Order::factory()
            ->delivered()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-DELV-001',
                'delivery_address_id' => $defaultAddress?->id,
                'delivery_person_id' => $deliveryPerson->id,
            ]);

        // Create cancelled order
        Order::factory()
            ->cancelled()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-CANC-001',
                'delivery_address_id' => $defaultAddress?->id,
            ]);

        $this->command->info('Demo orders created successfully.');
    }
}
