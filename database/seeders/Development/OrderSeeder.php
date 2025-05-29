<?php

// database/seeders/Development/OrderSeeder.php

namespace Database\Seeders\Development;

use App\Enums\DeliveryType;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\AccessoryType;
use App\Models\BottleType;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating orders for development...');

        $this->createConfirmedOrders();
        $this->createProcessingOrders();
        $this->createDeliveredOrders();
        $this->createCancelledOrders();

        $this->command->info('Development orders created successfully!');
    }

    /**
     * Create orders with confirmed status
     */
    private function createConfirmedOrders(): void
    {
        $this->command->info('Creating confirmed orders...');

        // Vérifier si on a des clients et des adresses de livraison
        $customers = User::role(UserRole::CUSTOMER()->value)->get();
        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        // Créer 8 commandes confirmées
        $confirmedOrders = [];
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Vérifier si le client a des adresses de livraison
            $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
            if ($deliveryAddresses->isEmpty()) {
                continue; // Skip this customer if no delivery address
            }

            $deliveryAddress = $deliveryAddresses->random();

            $confirmedOrders[] = Order::factory()
                ->confirmed()
                ->create([
                    'customer_id' => $customer->id,
                    'distribution_center_id' => $center->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'order_number' => 'ORD-'.rand(100000, 999999),
                    'order_date' => now()->subDays(rand(1, 10)),
                ]);
        }

        $this->addOrderItems($confirmedOrders);

        $this->command->info(count($confirmedOrders).' confirmed orders created.');
    }

    /**
     * Create orders with processing status
     */
    private function createProcessingOrders(): void
    {
        $this->command->info('Creating processing orders...');

        // Vérifier si on a des clients et des adresses de livraison
        $customers = User::role(UserRole::CUSTOMER()->value)->get();
        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $deliveryPersons = User::role(UserRole::DELIVERY_PERSON()->value)->get();

        // Créer 8 commandes en cours de traitement
        $processingOrders = [];
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $center = $centers->random();
            $deliveryPerson = $deliveryPersons->isNotEmpty() ? $deliveryPersons->random() : null;

            // Vérifier si le client a des adresses de livraison
            $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
            if ($deliveryAddresses->isEmpty()) {
                continue; // Skip this customer if no delivery address
            }

            $deliveryAddress = $deliveryAddresses->random();

            $processingOrders[] = Order::factory()
                ->processing()
                ->create([
                    'customer_id' => $customer->id,
                    'distribution_center_id' => $center->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'delivery_person_id' => $deliveryPerson?->id,
                    'order_number' => 'ORD-'.rand(100000, 999999),
                    'order_date' => now()->subDays(rand(1, 7)),
                ]);
        }

        $this->addOrderItems($processingOrders);

        $this->command->info(count($processingOrders).' processing orders created.');
    }

    /**
     * Create orders with delivered status
     */
    private function createDeliveredOrders(): void
    {
        $this->command->info('Creating delivered orders...');

        $deliveryPersons = User::role(UserRole::DELIVERY_PERSON()->value)->get();

        if ($deliveryPersons->isEmpty()) {
            $this->command->error('No delivery persons found. Cannot create delivered orders.');

            return;
        }

        // Vérifier si on a des clients et des adresses de livraison
        $customers = User::role(UserRole::CUSTOMER()->value)->get();
        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $deliveredOrders = [];

        foreach ($deliveryPersons as $deliveryPerson) {
            // Créer 1-4 commandes livrées par livreur
            $ordersPerDeliveryPerson = rand(1, 4);

            for ($i = 0; $i < $ordersPerDeliveryPerson; $i++) {
                $customer = $customers->random();
                $center = $centers->random();

                // Vérifier si le client a des adresses de livraison
                $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
                if ($deliveryAddresses->isEmpty()) {
                    continue; // Skip this customer if no delivery address
                }

                $deliveryAddress = $deliveryAddresses->random();

                $deliveredOrders[] = Order::factory()
                    ->delivered()
                    ->create([
                        'customer_id' => $customer->id,
                        'distribution_center_id' => $center->id,
                        'delivery_address_id' => $deliveryAddress->id,
                        'delivery_person_id' => $deliveryPerson->id,
                        'order_number' => 'ORD-'.rand(100000, 999999),
                        'order_date' => now()->subDays(rand(2, 5)),
                    ]);
            }
        }

        $this->addOrderItems($deliveredOrders);

        $this->command->info(count($deliveredOrders).' delivered orders created.');
    }

    /**
     * Create orders with cancelled status
     */
    private function createCancelledOrders(): void
    {
        $this->command->info('Creating cancelled orders...');

        // Vérifier si on a des clients et des adresses de livraison
        $customers = User::role(UserRole::CUSTOMER()->value)->get();
        if ($customers->isEmpty()) {
            $this->command->error('No customers found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $cancelledOrders = [];

        // Créer 7 commandes annulées
        for ($i = 0; $i < 7; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Vérifier si le client a des adresses de livraison
            $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
            if ($deliveryAddresses->isEmpty()) {
                continue; // Skip this customer if no delivery address
            }

            $deliveryAddress = $deliveryAddresses->random();

            $cancelledOrders[] = Order::factory()
                ->cancelled()
                ->create([
                    'customer_id' => $customer->id,
                    'distribution_center_id' => $center->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'order_number' => 'ORD-'.rand(100000, 999999),
                    'order_date' => now()->subDays(rand(5, 30)),
                ]);
        }

        $this->addOrderItems($cancelledOrders);

        $this->command->info(count($cancelledOrders).' cancelled orders created.');
    }

    /**
     * Add order items to orders
     */
    private function addOrderItems($orders): void
    {
        $bottleTypes = BottleType::all();
        $accessoryTypes = AccessoryType::all();

        if ($bottleTypes->isEmpty()) {
            $this->command->error('No bottle types found. Run BottleTypeSeeder first.');

            return;
        }

        foreach ($orders as $order) {
            // Ajouter 1 à 3 bouteilles à chaque commande
            $bottlesForOrder = $bottleTypes->random(rand(1, 3));

            foreach ($bottlesForOrder as $bottleType) {
                // Ajouter 1 à 3 bouteilles de chaque type
                $quantity = rand(1, 3);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $bottleType->id,
                    'product_type' => ProductType::BOTTLE(),
                    'quantity' => $quantity,
                    'unit_price' => $bottleType->refill_price,
                    'total_price' => $bottleType->refill_price * $quantity,
                ]);
            }

            // 30% de chance d'ajouter des accessoires
            if (rand(1, 100) <= 30 && ! $accessoryTypes->isEmpty()) {
                $accessoriesForOrder = $accessoryTypes->random(rand(1, 2));

                foreach ($accessoriesForOrder as $accessoryType) {
                    // Généralement 1 accessoire de chaque type
                    $quantity = 1;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $accessoryType->id,
                        'product_type' => ProductType::ACCESSORY(),
                        'quantity' => $quantity,
                        'unit_price' => $accessoryType->price,
                        'total_price' => $accessoryType->price * $quantity,
                    ]);
                }
            }

            // Mettre à jour le montant total de la commande
            $subtotal = $order->items->sum('total_price');
            $deliveryFee = $order->delivery_type == DeliveryType::FAST() ? 1000 : 0;

            $order->update([
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total_amount' => $subtotal + $deliveryFee,
            ]);
        }
    }
}
