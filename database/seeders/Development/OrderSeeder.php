<?php

// database/seeders/Development/OrderSeeder.php

namespace Database\Seeders\Development;

use App\Enums\BottleMovementType;
use App\Enums\BottleOrderType;
use App\Enums\BottleStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Accessory;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\Customer;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderSeeder extends Seeder
{
    // Compteurs pour suivre les modifications de statut des bouteilles
    private array $bottleStatusCounts = [];
    private array $initialStockStats = [];
    private array $orderTypeStats = [
        'confirmed' => 0,
        'processing' => 0,
        'delivered' => 0,
        'cancelled' => 0,
    ];

    private array $bottleTypeStats = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating orders for development...');

        // Récupérer les statistiques initiales
        $centers = DistributionCenter::all();
        $bottleTypes = BottleType::all();

        $this->command->info('===== ÉTAT INITIAL DES STOCKS AVANT TOUTE COMMANDE =====');
        foreach ($centers as $center) {
            $this->initialStockStats[$center->id] = $this->collectCenterStockStats($center, $bottleTypes);
            $this->displayStockStatistics($center, $bottleTypes);
        }
        $this->command->info('=============================================================');

        $this->createConfirmedOrders();
        $this->command->info('===== ÉTAT DES STOCKS APRÈS COMMANDES CONFIRMÉES =====');
        $this->displayAllCenterStats($centers, $bottleTypes);

        $this->createProcessingOrders();
        $this->command->info('===== ÉTAT DES STOCKS APRÈS COMMANDES EN TRAITEMENT =====');
        $this->displayAllCenterStats($centers, $bottleTypes);

        $this->createDeliveredOrders();
        $this->command->info('===== ÉTAT DES STOCKS APRÈS COMMANDES LIVRÉES =====');
        $this->displayAllCenterStats($centers, $bottleTypes);

        $this->createCancelledOrders();
        $this->command->info('===== ÉTAT FINAL DES STOCKS APRÈS TOUTES LES COMMANDES =====');
        $this->displayAllCenterStats($centers, $bottleTypes);

        // Résumé global
        $this->command->info('==============================================');
        $this->command->info('RÉSUMÉ DES COMMANDES CRÉÉES:');
        $this->command->info("Commandes confirmées: {$this->orderTypeStats['confirmed']}");
        $this->command->info("Commandes en traitement: {$this->orderTypeStats['processing']}");
        $this->command->info("Commandes livrées: {$this->orderTypeStats['delivered']}");
        $this->command->info("Commandes annulées: {$this->orderTypeStats['cancelled']}");
        $this->command->info('==============================================');

        // Récapitulatif des changements de statut de bouteilles
        $this->command->info('RÉSUMÉ DES CHANGEMENTS DE STATUT DE BOUTEILLES:');
        foreach ($this->bottleStatusCounts as $status => $count) {
            $this->command->info("Bouteilles {$status}: {$count}");
        }

        $this->command->info('Development orders created successfully!');
    }

    /**
     * Affiche les statistiques de stock pour tous les centres
     */
    private function displayAllCenterStats($centers, $bottleTypes): void
    {
        foreach ($centers as $center) {
            $this->displayStockStatistics($center, $bottleTypes);
        }
    }

    /**
     * Récupère les valeurs de stock depuis la table pivot
     */
    private function getPivotStockValues(int $centerId, int $bottleTypeId): array
    {
        // Find the product category for this bottle type
        $productCategory = \App\Models\ProductCategory::where('product_type', \App\Enums\ProductType::BOTTLE())
            ->where('product_type_id', $bottleTypeId)
            ->first();

        if (! $productCategory) {
            return [
                'empty' => 0,
                'filled' => 0,
            ];
        }

        $stockData = DB::table('product_category_distribution_center')
            ->where('distribution_center_id', $centerId)
            ->where('product_category_id', $productCategory->id)
            ->first();

        return [
            'empty' => $stockData ? (int) $stockData->stock_empty : 0,
            'filled' => $stockData ? (int) $stockData->stock_filled : 0,
        ];
    }

    /**
     * Récupère les valeurs de stock actuelles en comptant les bouteilles
     */
    private function getActualStockValues(int $centerId, int $bottleTypeId): array
    {
        $emptyCount = Bottle::where('bottle_type_id', $bottleTypeId)
            ->where('is_filled', false)
            ->where('status', BottleStatus::IN_STOCK())
            ->where('distribution_center_id', $centerId)
            ->count();

        $filledCount = Bottle::where('bottle_type_id', $bottleTypeId)
            ->where('is_filled', true)
            ->where('status', BottleStatus::IN_STOCK())
            ->where('distribution_center_id', $centerId)
            ->count();

        return [
            'empty' => $emptyCount,
            'filled' => $filledCount,
        ];
    }

    /**
     * Formate une différence pour l'affichage
     */
    private function formatDifference(int $diff): string
    {
        if ($diff === 0) {
            return '(identique)';
        }

        $sign = $diff > 0 ? '+' : '';

        return "({$sign}{$diff})";
    }

    /**
     * Collecte les statistiques de stock pour un centre
     */
    private function collectCenterStockStats(DistributionCenter $center, $bottleTypes): array
    {
        $stats = [];

        foreach ($bottleTypes as $bottleType) {
            $pivotValues = $this->getPivotStockValues($center->id, $bottleType->id);
            $actualValues = $this->getActualStockValues($center->id, $bottleType->id);

            $stats[$bottleType->id] = [
                'pivot' => $pivotValues,
                'actual' => $actualValues,
            ];
        }

        return $stats;
    }

    /**
     * Récupère et affiche les statistiques détaillées des stocks pour un centre
     */
    private function displayStockStatistics(DistributionCenter $center, $bottleTypes): void
    {
        $this->command->line('--------------------------------------------------');
        $this->command->line("STATISTIQUES DE STOCK POUR: {$center->name}");
        $this->command->line('--------------------------------------------------');

        $totalPivotEmpty = 0;
        $totalPivotFilled = 0;
        $totalActualEmpty = 0;
        $totalActualFilled = 0;

        foreach ($bottleTypes as $bottleType) {
            // Récupérer les valeurs du pivot
            $pivotValues = $this->getPivotStockValues($center->id, $bottleType->id);
            $pivotEmpty = $pivotValues['empty'];
            $pivotFilled = $pivotValues['filled'];

            // Récupérer les valeurs réelles (calculées à partir de la table des bouteilles)
            $actualValues = $this->getActualStockValues($center->id, $bottleType->id);
            $actualEmpty = $actualValues['empty'];
            $actualFilled = $actualValues['filled'];

            // Calculer les différences
            $emptyDiff = $actualEmpty - $pivotEmpty;
            $filledDiff = $actualFilled - $pivotFilled;

            // Formater les différences
            $emptyDiffFormatted = $this->formatDifference($emptyDiff);
            $filledDiffFormatted = $this->formatDifference($filledDiff);

            // Mettre à jour les totaux
            $totalPivotEmpty += $pivotEmpty;
            $totalPivotFilled += $pivotFilled;
            $totalActualEmpty += $actualEmpty;
            $totalActualFilled += $actualFilled;

            // Afficher les statistiques pour ce type de bouteille
            $this->command->line("{$bottleType->name}:");
            $this->command->line("  - PIVOT   : {$pivotEmpty} vides, {$pivotFilled} pleines");
            $this->command->line("  - BOTTLES : {$actualEmpty} vides {$emptyDiffFormatted}, {$actualFilled} pleines {$filledDiffFormatted}");

            if ($emptyDiff !== 0 || $filledDiff !== 0) {
                $this->command->warn("  ⚠️ DIFFÉRENCE DÉTECTÉE pour {$bottleType->name} dans {$center->name}");
            }

            // Si on a des statistiques initiales, calculer la différence depuis le début
            if (isset($this->initialStockStats[$center->id][$bottleType->id])) {
                $initialActualValues = $this->initialStockStats[$center->id][$bottleType->id]['actual'];
                $initialEmptyCount = $initialActualValues['empty'];
                $initialFilledCount = $initialActualValues['filled'];

                $totalEmptyDiff = $actualEmpty - $initialEmptyCount;
                $totalFilledDiff = $actualFilled - $initialFilledCount;

                if ($totalEmptyDiff !== 0 || $totalFilledDiff !== 0) {
                    $emptyDiffText = $this->formatDifference($totalEmptyDiff);
                    $filledDiffText = $this->formatDifference($totalFilledDiff);
                    $this->command->line("  - DIFF TOTAL: {$emptyDiffText} vides, {$filledDiffText} pleines depuis le début");
                }
            }
        }

        // Afficher les totaux
        $totalEmptyDiff = $totalActualEmpty - $totalPivotEmpty;
        $totalFilledDiff = $totalActualFilled - $totalPivotFilled;
        $totalEmptyDiffFormatted = $this->formatDifference($totalEmptyDiff);
        $totalFilledDiffFormatted = $this->formatDifference($totalFilledDiff);

        $this->command->line('--------------------------------------------------');
        $this->command->line('TOTAUX:');
        $this->command->line("  - PIVOT   : {$totalPivotEmpty} vides, {$totalPivotFilled} pleines, Total: ".($totalPivotEmpty + $totalPivotFilled));
        $this->command->line("  - BOTTLES : {$totalActualEmpty} vides {$totalEmptyDiffFormatted}, {$totalActualFilled} pleines {$totalFilledDiffFormatted}, Total: ".($totalActualEmpty + $totalActualFilled).' '.$this->formatDifference($totalEmptyDiff + $totalFilledDiff));

        if ($totalEmptyDiff !== 0 || $totalFilledDiff !== 0) {
            $this->command->warn("  ⚠️ DIFFÉRENCE TOTALE DÉTECTÉE pour {$center->name}");
        }

        $this->command->line('--------------------------------------------------');
    }

    /**
     * Create orders with confirmed status
     */
    private function createConfirmedOrders(): void
    {
        $this->command->info('Creating confirmed orders...');

        // Get customers with at least one delivery address
        $customers = Customer::whereHas('deliveryAddresses')->get();

        if ($customers->isEmpty()) {
            $this->command->error('No customers with delivery addresses found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        // Create 8 confirmed orders
        $confirmedOrders = [];
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Get a random delivery address for this customer
            $deliveryAddress = $customer->deliveryAddresses()->inRandomOrder()->first();

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
        $this->orderTypeStats['confirmed'] = count($confirmedOrders);

        $this->command->info(count($confirmedOrders).' confirmed orders created.');
    }

    /**
     * Create orders with processing status
     */
    private function createProcessingOrders(): void
    {
        $this->command->info('Creating processing orders...');

        // Get customers with at least one delivery address
        $customers = Customer::whereHas('deliveryAddresses')->get();

        if ($customers->isEmpty()) {
            $this->command->error('No customers with delivery addresses found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $deliveryPersons = DeliveryPerson::where('is_active', true)->get();
        if ($deliveryPersons->isEmpty()) {
            $this->command->warning('No active delivery persons found. Some orders might not have a delivery person assigned.');
        }

        // Create 8 processing orders
        $processingOrders = [];
        for ($i = 0; $i < 8; $i++) {
            $customer = $customers->random();
            $center = $centers->random();
            $deliveryPerson = $deliveryPersons->isNotEmpty() ? $deliveryPersons->random() : null;

            // Get a random delivery address for this customer
            $deliveryAddress = $customer->deliveryAddresses()->inRandomOrder()->first();

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
        $this->orderTypeStats['processing'] = count($processingOrders);

        $this->command->info(count($processingOrders).' processing orders created.');
    }

    /**
     * Create orders with delivered status
     */
    private function createDeliveredOrders(): void
    {
        $this->command->info('Creating delivered orders...');

        // Get customers with at least one delivery address
        $customers = Customer::whereHas('deliveryAddresses')->get();

        if ($customers->isEmpty()) {
            $this->command->error('No customers with delivery addresses found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $deliveryPersons = DeliveryPerson::where('is_active', true)->get();
        if ($deliveryPersons->isEmpty()) {
            $this->command->error('No active delivery persons found. Cannot create delivered orders.');

            return;
        }

        $deliveredOrders = [];

        foreach ($deliveryPersons as $deliveryPerson) {
            // Create 1-4 delivered orders per delivery person
            $ordersPerDeliveryPerson = rand(1, 4);

            for ($i = 0; $i < $ordersPerDeliveryPerson; $i++) {
                $customer = $customers->random();
                $center = $centers->random();

                // Get a random delivery address for this customer
                $deliveryAddress = $customer->deliveryAddresses()->inRandomOrder()->first();

                $order = Order::factory()
                    ->delivered()
                    ->create([
                        'customer_id' => $customer->id,
                        'distribution_center_id' => $center->id,
                        'delivery_address_id' => $deliveryAddress->id,
                        'delivery_person_id' => $deliveryPerson->id,
                        'order_number' => 'ORD-'.rand(100000, 999999),
                        'order_date' => now()->subDays(rand(2, 5)),
                        'rating' => rand(0, 100) <= 70 ? rand(30, 50) / 10 : null,
                        'comments' => rand(0, 100) <= 70 ? fake()->realText(150) : null,
                        'center_comments' => rand(0, 100) <= 40 ? fake()->realText(100) : null,
                    ]);

                $deliveredOrders[] = $order;
            }
        }

        $this->addOrderItems($deliveredOrders);
        $this->orderTypeStats['delivered'] = count($deliveredOrders);

        $this->command->info(count($deliveredOrders).' delivered orders created.');
    }

    /**
     * Create orders with cancelled status
     */
    private function createCancelledOrders(): void
    {
        $this->command->info('Creating cancelled orders...');

        // Get customers with at least one delivery address
        $customers = Customer::whereHas('deliveryAddresses')->get();

        if ($customers->isEmpty()) {
            $this->command->error('No customers with delivery addresses found. Run UserSeeder first.');

            return;
        }

        $centers = DistributionCenter::all();
        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found. Run DistributionCenterSeeder first.');

            return;
        }

        $cancelledOrders = [];

        // Create 7 cancelled orders
        for ($i = 0; $i < 7; $i++) {
            $customer = $customers->random();
            $center = $centers->random();

            // Get a random delivery address for this customer
            $deliveryAddress = $customer->deliveryAddresses()->inRandomOrder()->first();

            $cancelledOrders[] = Order::factory()
                ->cancelled()
                ->create([
                    'customer_id' => $customer->id,
                    'distribution_center_id' => $center->id,
                    'delivery_address_id' => $deliveryAddress->id,
                    'order_number' => 'ORD-'.rand(100000, 999999),
                    'order_date' => now()->subDays(rand(5, 30)),
                    // Add rating and comments for cancelled orders (50% chance)
                    'rating' => rand(0, 100) <= 50 ? rand(10, 40) / 10 : null,
                    'comments' => rand(0, 100) <= 50 ? fake()->realText(150) : null,
                    'center_comments' => rand(0, 100) <= 60 ? fake()->realText(100) : null,
                ]);
        }

        $this->addOrderItems($cancelledOrders);
        $this->orderTypeStats['cancelled'] = count($cancelledOrders);

        $this->command->info(count($cancelledOrders).' cancelled orders created.');
    }

    private function addOrderItems($orders): void
    {
        foreach ($orders as $order) {
            if (rand(1, 100) <= 70) {
                $this->addBottlesToOrder($order);
            } else {
                $this->addBottlesToOrder($order);
                $this->addAccessoriesToOrder($order);
            }

            $this->updateOrderTotals($order);
        }
    }

    private function addBottlesToOrder(Order $order): void
    {
        // Use our new method instead of the bottleTypeStocks relation
        $bottleStocks = $this->getBottleTypesWithStock($order->distributionCenter);

        if (empty($bottleStocks)) {
            $this->command->warn("No filled bottles available for order {$order->order_number}");

            return;
        }

        // Select a random subset of bottle types (between 1 and 3, or all if less than 3 are available)
        $count = min(rand(1, 3), count($bottleStocks));
        $keys = array_rand($bottleStocks, $count);
        if (! is_array($keys)) {
            $keys = [$keys]; // If only one key is returned, wrap it in an array
        }

        $selectedTypes = [];
        foreach ($keys as $key) {
            $selectedTypes[] = $bottleStocks[$key];
        }

        foreach ($selectedTypes as $bottleType) {
            $bottleOrderType = rand(1, 100) <= 40
                ? BottleOrderType::FULL()
                : BottleOrderType::RECHARGE();

            $maxQuantity = min($bottleType->pivot->stock_filled, 3);
            $quantity = rand(1, $maxQuantity);

            $this->createBottleOrderItem($order, $bottleType, $quantity, $bottleOrderType);

            // Statistics tracking by bottle type
            if (! isset($this->bottleTypeStats[$bottleType->id])) {
                $this->bottleTypeStats[$bottleType->id] = [
                    'name' => $bottleType->name,
                    'total_orders' => 0,
                    'total_bottles' => 0,
                    'by_status' => [],
                    'by_order_type' => [],
                ];
            }

            $this->bottleTypeStats[$bottleType->id]['total_orders']++;
            $this->bottleTypeStats[$bottleType->id]['total_bottles'] += $quantity;

            // By order status
            if (! isset($this->bottleTypeStats[$bottleType->id]['by_status'][$order->status->value])) {
                $this->bottleTypeStats[$bottleType->id]['by_status'][$order->status->value] = 0;
            }
            $this->bottleTypeStats[$bottleType->id]['by_status'][$order->status->value] += $quantity;

            // By bottle order type
            if (! isset($this->bottleTypeStats[$bottleType->id]['by_order_type'][$bottleOrderType->value])) {
                $this->bottleTypeStats[$bottleType->id]['by_order_type'][$bottleOrderType->value] = 0;
            }
            $this->bottleTypeStats[$bottleType->id]['by_order_type'][$bottleOrderType->value] += $quantity;
        }
    }

    private function addAccessoriesToOrder(Order $order): void
    {
        // Updated query to avoid using the 'product' relationship which no longer exists
        $accessories = Accessory::where('distribution_center_id', $order->distribution_center_id)
            ->where('quantity', '>', 0)
            ->with(['accessoryType'])
            ->get();

        if ($accessories->isEmpty()) {
            return;
        }

        $accessoriesByType = $accessories->groupBy('accessory_type_id');
        $selectedTypes = $accessoriesByType->random(min(rand(1, 2), $accessoriesByType->count()));

        foreach ($selectedTypes as $accessoriesOfSameType) {
            $accessory = $accessoriesOfSameType->first();
            $quantity = 1;

            $this->createAccessoryOrderItem($order, $accessory, $quantity);
        }
    }

    private function createBottleOrderItem(Order $order, BottleType $bottleType, int $quantity, BottleOrderType $bottleOrderType): void
    {
        $unitPrice = match ($bottleOrderType) {
            BottleOrderType::FULL() => $bottleType->bottle_with_content_price,
            BottleOrderType::RECHARGE() => $bottleType->content_price,
        };

        // Déterminer le statut de bouteille en fonction du statut de commande
        $targetBottleStatus = match ($order->status->value) {
            'processing' => BottleStatus::WITH_DELIVERY_PERSON(),
            'confirmed' => BottleStatus::IN_STOCK(), // Reste en stock jusqu'à ce que le livreur l'emporte
            'delivered' => BottleStatus::WITH_CLIENT(),
            'cancelled' => BottleStatus::IN_STOCK(),
            default => BottleStatus::IN_STOCK(),
        };

        // Trouver la catégorie de produit correspondant à ce type de bouteille
        $productCategory = \App\Models\ProductCategory::where('product_type', \App\Enums\ProductType::BOTTLE())
            ->where('product_type_id', $bottleType->id)
            ->first();

        if (! $productCategory) {
            Log::warning("Catégorie de produit non trouvée pour le type de bouteille {$bottleType->name}, ID: {$bottleType->id}");

            return;
        }

        // Trouver des bouteilles disponibles (IN_STOCK + pas liées à une commande active)
        // Requête mise à jour pour utiliser product_category_id au lieu de product_id
        $availableBottles = Bottle::where('bottle_type_id', $bottleType->id)
            ->where('distribution_center_id', $order->distribution_center_id)
            ->where('is_filled', true)  // Toujours des bouteilles pleines pour les commandes
            ->where('status', BottleStatus::IN_STOCK())  // Toujours à partir du stock
            ->take($quantity)
            ->get();

        $foundCount = $availableBottles->count();
        $missingCount = $quantity - $foundCount;

        if ($missingCount > 0) {
            Log::info("Stock insuffisant: manque {$missingCount} bouteilles de type {$bottleType->name} pour la commande #{$order->order_number}");
        }

        // Traiter les bouteilles trouvées
        foreach ($availableBottles as $bottle) {
            // Créer l'élément de commande
            OrderItem::create([
                'order_id' => $order->id,
                'product_category_id' => $productCategory->id,
                'quantity' => 1,
                'bottle_type' => $bottleOrderType->value,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice,
            ]);

            // Si le statut doit changer, mettre à jour et enregistrer le mouvement
            $oldStatus = $bottle->status;

            // Mettre à jour le statut
            $bottle->update(['status' => $targetBottleStatus]);

            // Suivi des changements de statut
            $statusKey = $oldStatus->value.' -> '.$targetBottleStatus->value;
            if (! isset($this->bottleStatusCounts[$statusKey])) {
                $this->bottleStatusCounts[$statusKey] = 0;
            }
            $this->bottleStatusCounts[$statusKey]++;

            // Enregistrer le mouvement approprié
            $this->createBottleMovement($bottle, $order, $targetBottleStatus);

            // IMPORTANT: Mettre à jour la table pivot pour refléter le changement de stock
            $this->updatePivotStockCountsAfterStatusChange($bottle);
        }
    }

    private function createAccessoryOrderItem(Order $order, Accessory $accessory, int $quantity): void
    {
        // Trouver la catégorie de produit correspondant à ce type d'accessoire
        $productCategory = \App\Models\ProductCategory::where('product_type', \App\Enums\ProductType::ACCESSORY())
            ->where('product_type_id', $accessory->accessory_type_id)
            ->first();

        if (! $productCategory) {
            Log::warning("Catégorie de produit non trouvée pour le type d'accessoire ID: {$accessory->accessory_type_id}");

            return;
        }

        OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $productCategory->id,
            'quantity' => $quantity,
            'bottle_type' => null,
            'unit_price' => $accessory->accessoryType->price,
            'total_price' => $accessory->accessoryType->price * $quantity,
        ]);
    }

    private function updateOrderTotals(Order $order): void
    {
        $order->refresh();

        $subtotal = $order->items->sum('total_price');
        $deliveryFee = $order->delivery_type->fee();
        $totalAmount = $subtotal + $deliveryFee;

        $order->update([
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ]);

        $this->createOrderPayment($order, $totalAmount);
    }

    /**
     * Create payment record for an order
     */
    private function createOrderPayment(Order $order, float $totalAmount): void
    {
        // Generate a random payment reference
        $paymentReference = 'PAY-'.strtoupper(substr(md5(uniqid()), 0, 10));
        $paymentStatus = PaymentStatus::PAID();

        // Determine payment method randomly
        $paymentMethods = PaymentMethod::cases();
        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

        // Determine payment date
        $paymentDate = match ($paymentStatus->value) {
            'paid' => $order->order_date,
            'pending', 'failed' => null,
            default => null,
        };

        // Create payment record
        $payment = \App\Models\OrderPayment::create([
            'order_id' => $order->id,
            'payment_reference' => $paymentReference,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'amount_paid' => $paymentStatus == PaymentStatus::PAID() ? $totalAmount : 0,
            'amount_due' => $paymentStatus == PaymentStatus::PAID() ? 0 : $totalAmount,
            'payment_date' => $paymentDate,
            'payment_notes' => null,
        ]);

        // Si la commande est annulée et qu'elle avait été payée, on crée un remboursement
        if ($order->status->value === 'cancelled' && $paymentStatus == PaymentStatus::PAID()) {
            $this->createRefundForCancelledOrder($order, $totalAmount);
        }
    }

    /**
     * Crée un enregistrement de mouvement de bouteille lors du changement de statut
     */
    private function createBottleMovement(Bottle $bottle, Order $order, BottleStatus $targetStatus): void
    {
        // Déterminer le type de mouvement en fonction du statut cible
        $movementType = match ($targetStatus) {
            BottleStatus::WITH_DELIVERY_PERSON() => BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
            BottleStatus::WITH_CLIENT() => BottleMovementType::DELIVERY_TO_CUSTOMER(),
            default => null
        };

        if (! $movementType) {
            Log::warning("Type de mouvement non pris en charge pour le statut {$targetStatus->value}");

            return;
        }

        // Déterminer les paramètres du mouvement
        $params = [
            'bottle_id' => $bottle->id,
            'distribution_center_id' => $order->distribution_center_id,
            'type' => $movementType,
            'created_at' => $order->updated_at ?? now()->subHours(rand(1, 24)),
        ];

        if ($targetStatus === BottleStatus::WITH_DELIVERY_PERSON()) {
            $params['user_id'] = $order->delivery_person_id ?? \App\Models\User::role('center_manager')->inRandomOrder()->first()->id;
            $params['delivery_person_id'] = $order->delivery_person_id;
            $params['notes'] = "Bouteille assignée au livreur pour la commande #{$order->order_number}";
        } elseif ($targetStatus === BottleStatus::WITH_CLIENT()) {
            $params['user_id'] = $order->delivery_person_id ?? \App\Models\User::role('center_manager')->inRandomOrder()->first()->id;
            $params['delivery_person_id'] = $order->delivery_person_id;
            $params['customer_id'] = $order->customer_id;
            $params['notes'] = "Bouteille livrée au client via la commande #{$order->order_number}";

            // Pour les commandes livrées, nous devons d'abord enregistrer le passage au livreur
            // si la bouteille était directement en stock avant
            if ($bottle->getOriginal('status') === BottleStatus::IN_STOCK()->value) {
                \App\Models\BottleMovement::create([
                    'bottle_id' => $bottle->id,
                    'user_id' => $order->delivery_person_id ?? \App\Models\User::role('center_manager')->inRandomOrder()->first()->id,
                    'delivery_person_id' => $order->delivery_person_id,
                    'distribution_center_id' => $order->distribution_center_id,
                    'type' => BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
                    'notes' => "Bouteille assignée au livreur pour la commande #{$order->order_number}",
                    'created_at' => now()->subHours(rand(24, 48)),
                ]);

                // Suivi des mouvements intermédiaires
                $intermediateKey = 'IN_STOCK -> WITH_DELIVERY_PERSON (intermédiaire)';
                if (! isset($this->bottleStatusCounts[$intermediateKey])) {
                    $this->bottleStatusCounts[$intermediateKey] = 0;
                }
                $this->bottleStatusCounts[$intermediateKey]++;
            }
        }

        // Créer l'enregistrement de mouvement
        \App\Models\BottleMovement::create($params);
    }

    /**
     * Met à jour les compteurs de stock dans la table pivot après un changement de statut
     * Cette étape est cruciale pour maintenir la cohérence entre le nombre réel de bouteilles
     * et les valeurs dans la table pivot product_category_distribution_center
     */
    private function updatePivotStockCountsAfterStatusChange(Bottle $bottle): void
    {
        // Si la bouteille quitte le stock, il faut décrémenter le compteur dans le pivot
        if ($bottle->status !== BottleStatus::IN_STOCK()) {
            // Trouver la catégorie de produit correspondant à ce type de bouteille
            $productCategory = \App\Models\ProductCategory::where('product_type', \App\Enums\ProductType::BOTTLE())
                ->where('product_type_id', $bottle->bottle_type_id)
                ->first();

            if (! $productCategory) {
                Log::warning("Product category not found for bottle #{$bottle->id} with type ID {$bottle->bottle_type_id}");

                return;
            }

            // Get current pivot data
            $pivotData = DB::table('product_category_distribution_center')
                ->where('distribution_center_id', $bottle->distribution_center_id)
                ->where('product_category_id', $productCategory->id)
                ->first();

            if (! $pivotData) {
                Log::warning("Pivot data not found for bottle #{$bottle->id} in center {$bottle->distribution_center_id}");

                return;
            }

            if ($bottle->is_filled) {
                $newFilledCount = max(0, $pivotData->stock_filled - 1);

                DB::table('product_category_distribution_center')
                    ->where('distribution_center_id', $bottle->distribution_center_id)
                    ->where('product_category_id', $productCategory->id)
                    ->update([
                        'stock_filled' => $newFilledCount,
                        'updated_at' => now(),
                    ]);
            } else {
                $newEmptyCount = max(0, $pivotData->stock_empty - 1);

                DB::table('product_category_distribution_center')
                    ->where('distribution_center_id', $bottle->distribution_center_id)
                    ->where('product_category_id', $productCategory->id)
                    ->update([
                        'stock_empty' => $newEmptyCount,
                        'updated_at' => now(),
                    ]);
            }
        }
        // Si la bouteille revient en stock, il faudrait incrémenter le compteur
        // Ce cas n'est pas géré ici car dans ce seeder nous ne retournons pas les bouteilles en stock
    }

    /**
     * Create refund record for a cancelled order
     */
    private function createRefundForCancelledOrder(Order $order, float $totalAmount): void
    {
        $refundAmount = $totalAmount;
        $initiatedBy = \App\Models\User::role('admin')->inRandomOrder()->first()?->id
            ?? \App\Models\User::role('center_manager')->inRandomOrder()->first()?->id
            ?? 1;

        $orderDate = $order->order_date;
        $cancelledAt = $orderDate->copy()->addHours(rand(1, 72));
        $cancelledAt = $cancelledAt->min(now()->subHours(1));
        $order->update([
            'cancelled_at' => $cancelledAt,
        ]);

        $completedAt = $cancelledAt->copy()->addHours(rand(1, 24));
        $completedAt = $completedAt->min(now());

        \App\Models\Refund::create([
            'order_id' => $order->id,
            'initiated_by' => $initiatedBy,
            'refund_method' => PaymentMethod::cases()[array_rand(PaymentMethod::cases())],
            'refund_identifier' => 'REF-'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'status' => PaymentStatus::PAID(),
            'amount' => $refundAmount,
            'reason' => 'Commande annulée par le client',
            'notes' => 'Remboursement automatique suite à annulation',
            'initiated_at' => $cancelledAt,
            'completed_at' => $completedAt,
        ]);

        $order->cancelled_by = $initiatedBy;
        $order->cancelled_reason = rand(0, 1) ? 'Demande du client ' : 'Problème technique au centre';
        $order->save();

        Log::info("Remboursement créé pour commande #{$order->order_number} d'un montant de {$refundAmount} CFA");
    }

    /**
     * Get bottle types with available stock for a distribution center
     * This replaces the use of DistributionCenter->bottleTypeStocks() relation
     */
    private function getBottleTypesWithStock(DistributionCenter $center): array
    {
        // Find all product categories that are bottle types with stock
        $stockData = DB::table('product_category_distribution_center as pcdc')
            ->join('product_categories as pc', 'pcdc.product_category_id', '=', 'pc.id')
            ->join('bottle_types as bt', 'pc.product_type_id', '=', 'bt.id')
            ->where('pcdc.distribution_center_id', $center->id)
            ->where('pc.product_type', 'bottle')
            ->where('pcdc.stock_filled', '>', 0)
            ->select('bt.*', 'pcdc.stock_filled', 'pcdc.stock_empty')
            ->get();

        // Format the data to mimic the previous bottleTypeStocks relation
        $result = [];
        foreach ($stockData as $item) {
            $bottleType = BottleType::find($item->id);
            if ($bottleType) {
                // Add pivot data to the bottle type
                $bottleType->pivot = (object) [
                    'stock_filled' => $item->stock_filled,
                    'stock_empty' => $item->stock_empty,
                ];
                $result[] = $bottleType;
            }
        }

        return $result;
    }
}
