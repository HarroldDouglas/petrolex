<?php

namespace Database\Seeders\Development;

use App\Enums\BottleStatus;
use App\Enums\UserRole;
use App\Models\Bottle;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDistributionCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    private DistributionCenter $demoCenter;

    public function run(): void
    {
        $this->command->info('Creating demo data for staging/demo environments...');

        $this->createDemoDistributionCenters();
        $this->createDemoUsers();
        $this->createDemoCustomerData();
        $this->createDemoBottlesAndOrders();

        $this->command->info('Demo data created successfully!');
    }

    private function createDemoDistributionCenters(): void
    {
        // Get or create a demo neighborhood (use existing one or create in Yaoundé I)
        $demoNeighborhood = \App\Models\Geography\Neighborhood::first();

        if (! $demoNeighborhood) {
            $this->command->error('No neighborhoods found. Please seed geographic data first.');

            return;
        }

        $this->demoCenter = DistributionCenter::firstOrCreate(
            ['name' => 'Demo Distribution Center'],
            [
                'neighborhood_id' => $demoNeighborhood->id,
                'address' => '123 Main Street, Demo City',
                'description' => 'Demo center for testing',
                'latitude' => 4.0511,
                'longitude' => 9.7679,
                'phone' => '123456789',
                'email' => 'demo.center@example.com',
                'is_active' => true,
            ]
        );

        $this->command->info('Demo distribution centers created.');
    }

    private function createDemoUsers(): void
    {
        $this->createManagerUser();
        $this->createCenterManagerUser();
        $this->createDeliveryPersonUser();
        $this->createCustomerUser();

        $this->command->info('Demo users created with password "demo123".');
    }

    private function createManagerUser(): void
    {
        $managerUser = User::firstOrCreate(
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
        );

        $managerUser->assignRole(UserRole::MANAGER()->value);
        $this->assignUserToCenter($managerUser);
    }

    private function createCenterManagerUser(): void
    {
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
        $this->assignUserToCenter($centerManagerUser);
    }

    private function createDeliveryPersonUser(): void
    {
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

        $deliveryPerson = DeliveryPerson::firstOrCreate(
            ['user_id' => $deliveryPersonUser->id],
            ['is_active' => true]
        );

        $this->assignDeliveryPersonToCenter($deliveryPerson);
    }

    private function createCustomerUser(): void
    {
        $customerUser = User::firstOrCreate(
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

        $customerUser->assignRole(UserRole::CUSTOMER()->value);

        Customer::firstOrCreate(
            ['user_id' => $customerUser->id],
            ['current_balance' => 0.00]
        );
    }

    private function assignUserToCenter(User $user): void
    {
        UserDistributionCenter::firstOrCreate(
            [
                'user_id' => $user->id,
                'distribution_center_id' => $this->demoCenter->id,
            ],
            [
                'is_active' => true,
            ]
        );

        $this->command->info("User {$user->email} assigned to center {$this->demoCenter->name}");
    }

    private function assignDeliveryPersonToCenter(DeliveryPerson $deliveryPerson): void
    {
        $exists = $deliveryPerson->distributionCenters()
            ->where('distribution_center_id', $this->demoCenter->id)
            ->exists();

        if (! $exists) {
            $deliveryPerson->distributionCenters()->attach($this->demoCenter->id, [
                'is_active' => true,
            ]);
            $this->command->info("Delivery person assigned to center {$this->demoCenter->name}");
        } else {
            $deliveryPerson->distributionCenters()->updateExistingPivot($this->demoCenter->id, [
                'is_active' => true,
                'updated_at' => now(),
            ]);
            $this->command->info('Updated existing delivery person assignment');
        }
    }

    private function createDemoCustomerData(): void
    {
        $customerRecord = Customer::whereHas('user', function (
            $query
        ) {
            $query->where('email', 'demo.customer@example.com');
        })->first();

        if (! $customerRecord) {
            $this->command->error('Demo customer not found. Create it first.');

            return;
        }

        // Get the first available neighborhood
        $neighborhood = \App\Models\Geography\Neighborhood::first();

        if (! $neighborhood) {
            $this->command->error('No neighborhoods found. Please seed geographic data first.');

            return;
        }

        CustomerDeliveryAddress::firstOrCreate(
            [
                'customer_id' => $customerRecord->id,
                'label' => 'Home',
            ],
            [
                'address' => '321 Demo Boulevard, Demo City',
                'latitude' => fake()->latitude(3.8, 4.1),
                'longitude' => fake()->longitude(9.6, 11.6),
                'phone' => $customerRecord->user->phone_number,
                'phone_country_code' => '+237',
                'contact_firstname' => $customerRecord->user->first_name,
                'contact_lastname' => $customerRecord->user->last_name,
                'email' => $customerRecord->user->email,
                'address_precision' => 'Près de la grande place',
                'is_default' => true,
                'neighborhood_id' => $neighborhood->id,
            ]
        );

        $this->command->info('Demo customer delivery address created.');
    }

    private function createDemoBottlesAndOrders(): void
    {
        $deliveryPerson = $this->getDeliveryPerson();
        $customerRecord = $this->getCustomerRecord();

        if (! $customerRecord || ! $deliveryPerson) {
            $this->command->error('Demo users not found.');

            return;
        }

        $this->createDemoBottles();
        $this->createDemoOrders($customerRecord, $deliveryPerson);
    }

    private function getDeliveryPerson(): ?DeliveryPerson
    {
        return DeliveryPerson::whereHas('user', function ($query) {
            $query->where('email', 'demo.delivery@example.com');
        })->first();
    }

    private function getCustomerRecord(): ?Customer
    {
        return Customer::whereHas('user', function ($query) {
            $query->where('email', 'demo.customer@example.com');
        })->first();
    }

    private function createDemoBottles(): void
    {
        Bottle::factory()
            ->count(5)
            ->create(['status' => BottleStatus::WITH_DELIVERY_PERSON()]);

        Bottle::factory()
            ->count(2)
            ->create(['status' => BottleStatus::WITH_CLIENT()]);

        $this->command->info('Demo bottles created.');
    }

    private function createDemoOrders(Customer $customerRecord, DeliveryPerson $deliveryPerson): void
    {
        $defaultAddress = $customerRecord->deliveryAddresses()->where('is_default', true)->first();

        if (! $defaultAddress) {
            $this->command->error('Demo customer has no default address.');

            return;
        }

        $this->createConfirmedOrder($customerRecord, $defaultAddress);
        $this->createProcessingOrder($customerRecord, $defaultAddress, $deliveryPerson);
        $this->createDeliveredOrder($customerRecord, $defaultAddress, $deliveryPerson);
        $this->createCancelledOrder($customerRecord, $defaultAddress);

        $this->command->info('Demo orders created successfully.');
    }

    private function createConfirmedOrder(Customer $customerRecord, CustomerDeliveryAddress $address): void
    {
        Order::factory()
            ->confirmed()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-CONF-001',
                'delivery_address_id' => $address->id,
            ]);
    }

    private function createProcessingOrder(Customer $customerRecord, CustomerDeliveryAddress $address, DeliveryPerson $deliveryPerson): void
    {
        Order::factory()
            ->processing()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-PROC-001',
                'delivery_address_id' => $address->id,
                'delivery_person_id' => $deliveryPerson->id,
            ]);
    }

    private function createDeliveredOrder(Customer $customerRecord, CustomerDeliveryAddress $address, DeliveryPerson $deliveryPerson): void
    {
        Order::factory()
            ->delivered()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-DELV-001',
                'delivery_address_id' => $address->id,
                'delivery_person_id' => $deliveryPerson->id,
            ]);
    }

    private function createCancelledOrder(Customer $customerRecord, CustomerDeliveryAddress $address): void
    {
        Order::factory()
            ->cancelled()
            ->create([
                'customer_id' => $customerRecord->id,
                'order_number' => 'DEMO-CANC-001',
                'delivery_address_id' => $address->id,
            ]);
    }
}
