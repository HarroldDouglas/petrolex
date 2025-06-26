<?php

namespace Database\Seeders\Development;

use App\Enums\ProductType;
use App\Enums\SupplierDeliveryStatus;
use App\Models\AccessoryType;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\SupplierDelivery;
use App\Models\SupplierDeliveryBottle;
use App\Models\SupplierDeliveryProductType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class SupplierDeliverySeeder extends Seeder
{
    /**
     * List of supplier names
     */
    protected array $suppliers = [
        'GazCam S.A.',
        'EnergieAfrique',
        'PetroGaz',
        'AfricaGas',
        'CameroonEnergy',
    ];

    /**
     * Seed the database with supplier deliveries.
     */
    public function run(): void
    {
        $this->command->info('Creating supplier deliveries...');

        $resources = $this->validateAndGetResources();
        if (! $resources) {
            return;
        }

        [$distributionCenters, $users, $bottleTypes, $accessoryTypes] = $resources;

        $this->generateDeliveriesForAllCenters(
            $distributionCenters,
            $users,
            $bottleTypes,
            $accessoryTypes
        );

        $this->command->info('Supplier deliveries created successfully!');
    }

    /**
     * Validate prerequisites and get necessary resources
     *
     * @return array|null Returns array of resources or null if validation failed
     */
    protected function validateAndGetResources(): ?array
    {
        // Get distribution centers
        $distributionCenters = DistributionCenter::all();
        if ($distributionCenters->isEmpty()) {
            $this->command->error('No distribution centers found! Please run DistributionCenterSeeder first.');

            return null;
        }

        // Get admin users
        $users = User::role('admin')->get();
        if ($users->isEmpty()) {
            $this->command->error('No admin users found! Please run UserSeeder first.');

            return null;
        }

        // Get product types
        $bottleTypes = BottleType::all();
        $accessoryTypes = AccessoryType::all();

        if ($bottleTypes->isEmpty() || $accessoryTypes->isEmpty()) {
            $this->command->error('No bottle types or accessory types found! Please run the required seeders first.');

            return null;
        }

        return [$distributionCenters, $users, $bottleTypes, $accessoryTypes];
    }

    /**
     * Generate deliveries for all distribution centers
     */
    protected function generateDeliveriesForAllCenters(
        Collection $distributionCenters,
        Collection $users,
        Collection $bottleTypes,
        Collection $accessoryTypes
    ): void {
        foreach ($distributionCenters as $center) {
            $this->generateDeliveriesForCenter($center, $users, $bottleTypes, $accessoryTypes);
        }
    }

    /**
     * Generate deliveries with different statuses for a specific center
     */
    protected function generateDeliveriesForCenter(
        DistributionCenter $center,
        Collection $users,
        Collection $bottleTypes,
        Collection $accessoryTypes
    ): void {
        $statuses = [
            SupplierDeliveryStatus::IN_PROGRESS(),
            SupplierDeliveryStatus::COMPLETED(),
            SupplierDeliveryStatus::CANCELLED(),
        ];

        foreach ($statuses as $status) {
            $this->createSupplierDelivery(
                $center,
                $users->random(),
                $status,
                $bottleTypes,
                $accessoryTypes
            );
        }
    }

    /**
     * Create a supplier delivery with associated products
     */
    protected function createSupplierDelivery(
        DistributionCenter $center,
        User $user,
        SupplierDeliveryStatus $status,
        Collection $bottleTypes,
        Collection $accessoryTypes
    ): SupplierDelivery {
        $delivery = $this->createDeliveryRecord($center, $user, $status);
        $this->addBottleProductTypes($delivery, $bottleTypes, $center, $status);
        $this->addAccessoryProductTypes($delivery, $accessoryTypes);

        return $delivery;
    }

    /**
     * Create the main delivery record
     */
    protected function createDeliveryRecord(
        DistributionCenter $center,
        User $user,
        SupplierDeliveryStatus $status
    ): SupplierDelivery {
        return SupplierDelivery::create([
            'distribution_center_id' => $center->id,
            'user_id' => $user->id,
            'delivery_number' => $this->generateDeliveryNumber(),
            'supplier_name' => $this->getRandomSupplierName(),
            'description' => "Supply for {$center->name}",
            'supply_date' => Carbon::now()->subDays(rand(1, 30)),
            'status' => $status->value,
            'notes' => $this->getNotesForStatus($status),
        ]);
    }

    /**
     * Generate a unique delivery number
     */
    protected function generateDeliveryNumber(): string
    {
        return 'APR-'.rand(1000, 9999);
    }

    /**
     * Get a random supplier name
     */
    protected function getRandomSupplierName(): string
    {
        return $this->suppliers[array_rand($this->suppliers)];
    }

    /**
     * Generate appropriate notes based on delivery status
     */
    protected function getNotesForStatus(SupplierDeliveryStatus $status): ?string
    {
        if ($status === SupplierDeliveryStatus::CANCELLED()) {
            return 'Cancelled due to logistical issues';
        }

        return null;
    }

    /**
     * Add bottle product types to the delivery
     */
    protected function addBottleProductTypes(
        SupplierDelivery $delivery,
        Collection $bottleTypes,
        DistributionCenter $center,
        SupplierDeliveryStatus $status
    ): void {
        $selectedBottleTypes = $bottleTypes->random(rand(1, 3));

        foreach ($selectedBottleTypes as $bottleType) {
            $expectedQuantity = rand(10, 50);

            $productType = $this->createBottleProductType($delivery, $bottleType, $expectedQuantity, $status);

            if ($status === SupplierDeliveryStatus::COMPLETED()) {
                $this->addBottlesToCompletedDelivery($delivery, $productType, $bottleType, $center, $expectedQuantity);
            }
        }
    }

    /**
     * Create a bottle product type entry
     */
    protected function createBottleProductType(
        SupplierDelivery $delivery,
        BottleType $bottleType,
        int $expectedQuantity,
        SupplierDeliveryStatus $status
    ): SupplierDeliveryProductType {
        return SupplierDeliveryProductType::create([
            'supplier_delivery_id' => $delivery->id,
            'product_type' => ProductType::BOTTLE()->value,
            'bottle_type_id' => $bottleType->id,
            'accessory_type_id' => null,
            'expected_quantity' => $expectedQuantity,
            'bottles_out_quantity' => $status === SupplierDeliveryStatus::COMPLETED() ? rand(0, 5) : 0,
        ]);
    }

    /**
     * Add specific bottles to a completed delivery
     */
    protected function addBottlesToCompletedDelivery(
        SupplierDelivery $delivery,
        SupplierDeliveryProductType $productType,
        BottleType $bottleType,
        DistributionCenter $center,
        int $expectedQuantity
    ): void {
        $availableBottles = $this->getAvailableBottles($bottleType, $center, $expectedQuantity);

        foreach ($availableBottles as $bottle) {
            $this->linkBottleToDelivery($delivery, $productType, $bottle);
        }
    }

    /**
     * Get available bottles for a specific type and center
     */
    protected function getAvailableBottles(
        BottleType $bottleType,
        DistributionCenter $center,
        int $limit
    ): Collection {
        return Bottle::ofBottleType($bottleType->id)
            ->where('distribution_center_id', $center->id)
            ->take($limit)
            ->get();
    }

    /**
     * Link a bottle to a delivery
     */
    protected function linkBottleToDelivery(
        SupplierDelivery $delivery,
        SupplierDeliveryProductType $productType,
        Bottle $bottle
    ): void {
        SupplierDeliveryBottle::create([
            'supplier_delivery_product_type_id' => $productType->id,
            'bottle_id' => $bottle->id,
        ]);
    }

    /**
     * Add accessory product types to the delivery
     */
    protected function addAccessoryProductTypes(
        SupplierDelivery $delivery,
        Collection $accessoryTypes
    ): void {
        // 50% chance to have accessories
        if (rand(0, 1) === 0) {
            return;
        }

        $selectedAccessoryTypes = $accessoryTypes->random(rand(1, 2));

        foreach ($selectedAccessoryTypes as $accessoryType) {
            $this->createAccessoryProductType($delivery, $accessoryType);
        }
    }

    /**
     * Create an accessory product type entry
     */
    protected function createAccessoryProductType(
        SupplierDelivery $delivery,
        AccessoryType $accessoryType
    ): SupplierDeliveryProductType {
        return SupplierDeliveryProductType::create([
            'supplier_delivery_id' => $delivery->id,
            'product_type' => ProductType::ACCESSORY()->value,
            'bottle_type_id' => null,
            'accessory_type_id' => $accessoryType->id,
            'expected_quantity' => rand(5, 20),
            'bottles_out_quantity' => 0, // Not applicable for accessories
        ]);
    }
}
