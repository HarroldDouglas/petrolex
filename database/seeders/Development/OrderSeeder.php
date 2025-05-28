<?php

// database/seeders/Development/OrderSeeder.php

namespace Database\Seeders\Development;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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

        $this->createPendingOrders();
        $this->createConfirmedOrders();
        $this->createProcessingOrders();
        $this->createAssignedOrders();
        $this->createInTransitOrders();
        $this->createDeliveredOrders();
        $this->createCompletedOrders();
        $this->createCancelledOrders();
        $this->createReturnedOrders();

        $this->command->info('Development orders created successfully!');
    }

    /**
     * Create orders with pending status (nouvellement créées)
     */
    private function createPendingOrders(): void
    {
        $this->command->info('Creating pending orders...');

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

        // Créer 10 commandes en attente
        $pendingOrders = [];
        for ($i = 0; $i < 10; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Vérifier si le client a des adresses de livraison
            $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
            if ($deliveryAddresses->isEmpty()) {
                continue; // Skip this customer if no delivery address
            }

            $deliveryAddress = $deliveryAddresses->random();

            $pendingOrders[] = Order::create([
                'customer_id' => $customer->id,
                'distribution_center_id' => $center->id,
                'delivery_address_id' => $deliveryAddress->id,
                'order_number' => 'ORD-'.rand(100000, 999999),
                'order_date' => now()->subDays(rand(1, 15)),
                'status' => OrderStatus::PENDING(),
                'payment_method' => PaymentMethod::MOBILE_MONEY(),
                'payment_status' => PaymentStatus::PENDING(),
                'subtotal' => 0, // Will be updated after adding items
                'delivery_fee' => 0, // Will be updated after adding items
                'total_amount' => 0, // Will be updated after adding items
                'notes' => null,
            ]);
        }

        $this->addOrderItems($pendingOrders);

        $this->command->info(count($pendingOrders).' pending orders created.');
    }

    /**
     * Create orders with confirmed status (validées par le système)
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

            $confirmedOrders[] = Order::create([
                'customer_id' => $customer->id,
                'distribution_center_id' => $center->id,
                'delivery_address_id' => $deliveryAddress->id,
                'order_number' => 'ORD-'.rand(100000, 999999),
                'order_date' => now()->subDays(rand(1, 10)),
                'status' => OrderStatus::CONFIRMED(),
                'payment_method' => PaymentMethod::MOBILE_MONEY(),
                'payment_status' => PaymentStatus::PROCESSING(),
                'subtotal' => 0, // Will be updated after adding items
                'delivery_fee' => 0, // Will be updated after adding items
                'total_amount' => 0, // Will be updated after adding items
                'notes' => null,
            ]);
        }

        $this->addOrderItems($confirmedOrders);

        $this->command->info(count($confirmedOrders).' confirmed orders created.');
    }

    /**
     * Create orders with processing status (en cours de traitement)
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

        // Créer 8 commandes en cours de traitement
        $processingOrders = [];
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Vérifier si le client a des adresses de livraison
            $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
            if ($deliveryAddresses->isEmpty()) {
                continue; // Skip this customer if no delivery address
            }

            $deliveryAddress = $deliveryAddresses->random();

            $processingOrders[] = Order::create([
                'customer_id' => $customer->id,
                'distribution_center_id' => $center->id,
                'delivery_address_id' => $deliveryAddress->id,
                'order_number' => 'ORD-'.rand(100000, 999999),
                'order_date' => now()->subDays(rand(1, 7)),
                'status' => OrderStatus::PROCESSING(),
                'payment_method' => PaymentMethod::MOBILE_MONEY(),
                'payment_status' => PaymentStatus::PROCESSING(),
                'subtotal' => 0, // Will be updated after adding items
                'delivery_fee' => 0, // Will be updated after adding items
                'total_amount' => 0, // Will be updated after adding items
                'notes' => null,
            ]);
        }

        $this->addOrderItems($processingOrders);

        $this->command->info(count($processingOrders).' processing orders created.');
    }

    /**
     * Create orders with assigned status (affectées à un livreur)
     */
    private function createAssignedOrders(): void
    {
        $this->command->info('Creating assigned orders...');

        $deliveryPersons = User::role(UserRole::DELIVERY_PERSON()->value)->get();

        if ($deliveryPersons->isEmpty()) {
            $this->command->error('No delivery persons found. Cannot create assigned orders.');

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

        $assignedOrders = [];

        foreach ($deliveryPersons as $deliveryPerson) {
            $ordersPerDeliveryPerson = rand(1, 3);

            for ($i = 0; $i < $ordersPerDeliveryPerson; $i++) {
                $customer = $customers->random();
                $center = $centers->random();

                $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
                if ($deliveryAddresses->isEmpty()) {
                    continue;
                }

                $deliveryAddress = $deliveryAddresses->random();

                $assignedOrders[] = Order::create([
                    'customer_id' => $customer->id,
                    'distribution_center_id' => $center->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'delivery_person_id' => $deliveryPerson->id,
                    'order_number' => 'ORD-'.rand(100000, 999999),
                    'order_date' => now()->subDays(rand(1, 5)),
                    'status' => OrderStatus::ASSIGNED(),
                    'payment_method' => PaymentMethod::MOBILE_MONEY(),
                    'payment_status' => PaymentStatus::PENDING(),
                    'subtotal' => 0, // Will be updated after adding items
                    'delivery_fee' => 0, // Will be updated after adding items
                    'total_amount' => 0, // Will be updated after adding items
                    'notes' => null,
                ]);
            }
        }

        $this->addOrderItems($assignedOrders);

        $this->command->info(count($assignedOrders).' assigned orders created.');
    }

    /**
     * Create orders with in transit status (en cours de livraison)
     */
    private function createInTransitOrders(): void
    {
        $this->command->info('Creating in-transit orders...');

        $deliveryPersons = User::role(UserRole::DELIVERY_PERSON()->value)->get();

        if ($deliveryPersons->isEmpty()) {
            $this->command->error('No delivery persons found. Cannot create in-transit orders.');

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

        $inTransitOrders = [];

        foreach ($deliveryPersons as $deliveryPerson) {
            // Créer 0-2 commandes en transit par livreur
            $ordersPerDeliveryPerson = rand(0, 2);

            for ($i = 0; $i < $ordersPerDeliveryPerson; $i++) {
                $customer = $customers->random();
                $center = $centers->random();

                // Vérifier si le client a des adresses de livraison
                $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
                if ($deliveryAddresses->isEmpty()) {
                    continue; // Skip this customer if no delivery address
                }

                $deliveryAddress = $deliveryAddresses->random();

                $inTransitOrders[] = Order::create([
                    'customer_id' => $customer->id,
                    'distribution_center_id' => $center->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'delivery_person_id' => $deliveryPerson->id,
                    'order_number' => 'ORD-'.rand(100000, 999999),
                    'order_date' => now()->subDays(rand(1, 3)),
                    'status' => OrderStatus::IN_TRANSIT(),
                    'payment_method' => PaymentMethod::MOBILE_MONEY(),
                    'payment_status' => PaymentStatus::PENDING(),
                    'subtotal' => 0, // Will be updated after adding items
                    'delivery_fee' => 0, // Will be updated after adding items
                    'total_amount' => 0, // Will be updated after adding items
                    'notes' => null,
                ]);
            }
        }

        $this->addOrderItems($inTransitOrders);

        $this->command->info(count($inTransitOrders).' in-transit orders created.');
    }

    /**
     * Create orders with delivered status (livrées mais pas encore complétées)
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
                // 80% des commandes livrées sont payées, 20% en attente de paiement
                $paymentStatus = rand(1, 100) <= 80 ? PaymentStatus::PAID() : PaymentStatus::PENDING();

                // Générer des notes de livraison cohérentes
                $deliveryNotes = null;
                if ($paymentStatus->equals(PaymentStatus::PAID())) {
                    $possibleNotes = [
                        'Livraison effectuée sans problème. Client satisfait.',
                        'Client présent à la livraison.',
                        'Bouteille déposée à l\'emplacement indiqué par le client.',
                    ];
                    $deliveryNotes = $possibleNotes[array_rand($possibleNotes)];
                }

                $customer = $customers->random();
                $center = $centers->random();

                // Vérifier si le client a des adresses de livraison
                $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
                if ($deliveryAddresses->isEmpty()) {
                    continue; // Skip this customer if no delivery address
                }

                $deliveryAddress = $deliveryAddresses->random();

                $deliveredOrders[] = Order::create([
                    'customer_id' => $customer->id,
                    'distribution_center_id' => $center->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'delivery_person_id' => $deliveryPerson->id,
                    'order_number' => 'ORD-'.rand(100000, 999999),
                    'order_date' => now()->subDays(rand(2, 5)),
                    'delivery_date' => now()->subDays(rand(1, 3)), // Livrée récemment
                    'status' => OrderStatus::DELIVERED(),
                    'payment_method' => PaymentMethod::MOBILE_MONEY(),
                    'payment_status' => $paymentStatus,
                    'subtotal' => 0, // Will be updated after adding items
                    'delivery_fee' => 0, // Will be updated after adding items
                    'total_amount' => 0, // Will be updated after adding items
                    'notes' => $deliveryNotes,
                ]);
            }
        }

        $this->addOrderItems($deliveredOrders);

        $this->command->info(count($deliveredOrders).' delivered orders created.');
    }

    /**
     * Create orders with completed status (entièrement finalisées)
     */
    private function createCompletedOrders(): void
    {
        $this->command->info('Creating completed orders...');

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

        $completedOrders = [];

        // Créer 15 commandes complétées
        for ($i = 0; $i < 15; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Vérifier si le client a des adresses de livraison
            $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
            if ($deliveryAddresses->isEmpty()) {
                continue; // Skip this customer if no delivery address
            }

            $deliveryAddress = $deliveryAddresses->random();

            // Ajouter des notes cohérentes pour les commandes complétées
            $possibleNotes = [
                'Livraison effectuée avec succès. Client très satisfait.',
                'Client régulier, bonne transaction.',
                'Tout s\'est bien passé, paiement reçu.',
                'Livraison complétée avec retour de bouteille vide.',
            ];
            $notes = $possibleNotes[array_rand($possibleNotes)];

            $completedOrders[] = Order::create([
                'customer_id' => $customer->id,
                'distribution_center_id' => $center->id,
                'delivery_address_id' => $deliveryAddress->id,
                'delivery_person_id' => $deliveryPersons->isNotEmpty() ? $deliveryPersons->random()->id : null,
                'order_number' => 'ORD-'.rand(100000, 999999),
                'order_date' => now()->subDays(rand(10, 60)),
                'delivery_date' => now()->subDays(rand(5, 30)), // Livrée il y a un certain temps
                'status' => OrderStatus::COMPLETED(),
                'payment_method' => PaymentMethod::MOBILE_MONEY(),
                'payment_status' => PaymentStatus::PAID(), // Toutes les commandes complétées sont payées
                'subtotal' => 0, // Will be updated after adding items
                'delivery_fee' => 0, // Will be updated after adding items
                'total_amount' => 0, // Will be updated after adding items
                'notes' => $notes,
            ]);
        }

        $this->addOrderItems($completedOrders);

        $this->command->info(count($completedOrders).' completed orders created.');
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

            // 10% remboursés, 90% jamais payés
            $paymentStatus = rand(1, 100) <= 10 ? PaymentStatus::REFUNDED() : PaymentStatus::PENDING();

            // Ajouter des raisons d'annulation cohérentes
            $possibleNotes = [
                'Client a annulé la commande.',
                'Client non joignable après plusieurs tentatives.',
                'Stock insuffisant pour satisfaire la commande.',
                'Zone de livraison hors périmètre.',
                'Doublon de commande, annulée à la demande du client.',
            ];
            $notes = $possibleNotes[array_rand($possibleNotes)];

            $cancelledOrders[] = Order::create([
                'customer_id' => $customer->id,
                'distribution_center_id' => $center->id,
                'delivery_address_id' => $deliveryAddress->id,
                'order_number' => 'ORD-'.rand(100000, 999999),
                'order_date' => now()->subDays(rand(5, 30)),
                'status' => OrderStatus::CANCELLED(),
                'payment_method' => PaymentMethod::MOBILE_MONEY(),
                'payment_status' => $paymentStatus,
                'subtotal' => 0, // Will be updated after adding items
                'delivery_fee' => 0, // Will be updated after adding items
                'total_amount' => 0, // Will be updated after adding items
                'notes' => $notes,
            ]);
        }

        $this->addOrderItems($cancelledOrders);

        $this->command->info(count($cancelledOrders).' cancelled orders created.');
    }

    /**
     * Create orders with returned status
     */
    private function createReturnedOrders(): void
    {
        $this->command->info('Creating returned orders...');

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

        $returnedOrders = [];

        // Créer 5 commandes retournées
        for ($i = 0; $i < 5; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Vérifier si le client a des adresses de livraison
            $deliveryAddresses = CustomerDeliveryAddress::where('customer_id', $customer->id)->get();
            if ($deliveryAddresses->isEmpty()) {
                continue; // Skip this customer if no delivery address
            }

            $deliveryAddress = $deliveryAddresses->random();

            // 60% remboursés, 40% en attente
            $paymentStatus = rand(1, 100) <= 60 ? PaymentStatus::REFUNDED() : PaymentStatus::PENDING();

            // Ajouter des raisons de retour cohérentes
            $possibleNotes = [
                'Client absent lors de la livraison, colis retourné.',
                'Bouteille défectueuse retournée par le client.',
                'Erreur dans la commande, produit retourné.',
                'Adresse incorrecte, impossible de livrer.',
            ];
            $notes = $possibleNotes[array_rand($possibleNotes)];

            $returnedOrders[] = Order::create([
                'customer_id' => $customer->id,
                'distribution_center_id' => $center->id,
                'delivery_address_id' => $deliveryAddress->id,
                'delivery_person_id' => $deliveryPersons->isNotEmpty() ? $deliveryPersons->random()->id : null,
                'order_number' => 'ORD-'.rand(100000, 999999),
                'order_date' => now()->subDays(rand(5, 20)),
                'status' => OrderStatus::RETURNED(),
                'payment_method' => PaymentMethod::MOBILE_MONEY(),
                'payment_status' => $paymentStatus,
                'subtotal' => 0, // Will be updated after adding items
                'delivery_fee' => 0, // Will be updated after adding items
                'total_amount' => 0, // Will be updated after adding items
                'notes' => $notes,
            ]);
        }

        $this->addOrderItems($returnedOrders);

        $this->command->info(count($returnedOrders).' returned orders created.');
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
