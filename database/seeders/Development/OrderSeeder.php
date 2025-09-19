<?php

// database/seeders/Development/OrderSeeder.php

namespace Database\Seeders\Development;

use App\Enums\BottleMovementType;
use App\Enums\BottleOrderType;
use App\Enums\BottleStatus;
use App\Enums\Currency;
use App\Enums\NotificationType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ProductType;
use App\Enums\UserRole;
use App\Models\Accessory;
use App\Models\Bottle;
use App\Models\BottleMovement;
use App\Models\Customer;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderBottleScans;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\ProductCategory;
use App\Models\ProductCategoryDistributionCenter;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\OrderNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class OrderSeeder extends Seeder
{
    // Counters to track bottle status changes
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

        // Get initial statistics
        $centers = DistributionCenter::all();
        $productCategories = ProductCategory::bottles()->get();

        $this->command->info('===== ÉTAT INITIAL DES STOCKS AVANT TOUTE COMMANDE =====');
        foreach ($centers as $center) {
            $this->initialStockStats[$center->id] = $this->collectCenterStockStats($center, $productCategories);
            $this->displayStockStatistics($center, $productCategories);
        }
        $this->command->info('=============================================================');

        $this->createConfirmedOrders();
        $this->command->info('===== ÉTAT DES STOCKS APRÈS COMMANDES CONFIRMÉES =====');
        $this->displayAllCenterStats($centers, $productCategories);

        $this->createProcessingOrders();
        $this->command->info('===== ÉTAT DES STOCKS APRÈS COMMANDES EN TRAITEMENT =====');
        $this->displayAllCenterStats($centers, $productCategories);

        $this->createDeliveredOrders();
        $this->command->info('===== STOCK STATE AFTER DELIVERED ORDERS =====');
        $this->displayAllCenterStats($centers, $productCategories);

        $this->createCancelledOrders();
        $this->command->info('===== FINAL STOCK STATE AFTER ALL ORDERS =====');
        $this->displayAllCenterStats($centers, $productCategories);

        // Create specific test orders for customer1
        $this->createTestOrdersForCustomer1();

        // Global summary
        $this->displayOrderSummary();
        $this->createNotificationsForOrders();

        $this->command->info('Development orders created successfully!');
    }

    /**
     * Display a summary of created orders
     */
    private function displayOrderSummary(): void
    {
        $this->command->info('==============================================');
        $this->command->info('ORDERS CREATED SUMMARY:');
        $this->command->info("Confirmed orders: {$this->orderTypeStats['confirmed']}");
        $this->command->info("Processing orders: {$this->orderTypeStats['processing']}");
        $this->command->info("Delivered orders: {$this->orderTypeStats['delivered']}");
        $this->command->info("Cancelled orders: {$this->orderTypeStats['cancelled']}");
        $this->command->info('==============================================');

        // Bottle status change summary
        $this->command->info('BOTTLE STATUS CHANGE SUMMARY:');
        foreach ($this->bottleStatusCounts as $status => $count) {
            $this->command->info("Bottles {$status}: {$count}");
        }
    }

    /**
     * Display stock statistics for all centers
     */
    private function displayAllCenterStats(Collection $centers, Collection $productCategories): void
    {
        foreach ($centers as $center) {
            $this->displayStockStatistics($center, $productCategories);
        }
    }

    /**
     * Get stock values from pivot table
     */
    private function getPivotStockValues(int $centerId, int $productCategoryId): array
    {
        // Find the product category for this bottle type
        $productCategory = ProductCategory::find($productCategoryId);

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
     * Get actual stock values by counting bottles
     */
    private function getActualStockValues(int $centerId, int $productCategoryId): array
    {
        $pivotRecord = ProductCategoryDistributionCenter::where('distribution_center_id', $centerId)
            ->where('product_category_id', $productCategoryId)
            ->first();

        if (! $pivotRecord) {
            return [
                'empty' => 0,
                'filled' => 0,
            ];
        }

        $productCategory = ProductCategory::find($productCategoryId);

        if ($productCategory && $productCategory->product_type === \App\Enums\ProductType::BOTTLE()) {
            return [
                'empty' => $pivotRecord->stock_empty,
                'filled' => $pivotRecord->stock_filled,
            ];
        }

        return [
            'empty' => 0,
            'filled' => 0,
        ];
    }

    /**
     * Format a difference for display
     */
    private function formatDifference(int $diff): string
    {
        if ($diff === 0) {
            return '(identical)';
        }

        $sign = $diff > 0 ? '+' : '';

        return "({$sign}{$diff})";
    }

    /**
     * Collect stock statistics for a center
     */
    private function collectCenterStockStats(DistributionCenter $center, Collection $productCategories): array
    {
        $stats = [];

        foreach ($productCategories as $productCategory) {
            $pivotValues = $this->getPivotStockValues($center->id, $productCategory->id);
            $actualValues = $this->getActualStockValues($center->id, $productCategory->id);

            $stats[$productCategory->id] = [
                'pivot' => $pivotValues,
                'actual' => $actualValues,
            ];
        }

        return $stats;
    }

    /**
     * Retrieve and display detailed stock statistics for a center
     */
    private function displayStockStatistics(DistributionCenter $center, Collection $productCategories): void
    {
        $this->command->line('--------------------------------------------------');
        $this->command->line("STOCK STATISTICS FOR: {$center->name}");
        $this->command->line('--------------------------------------------------');

        $totalPivotEmpty = 0;
        $totalPivotFilled = 0;
        $totalActualEmpty = 0;
        $totalActualFilled = 0;

        foreach ($productCategories as $productCategory) {
            // Get pivot values
            $pivotValues = $this->getPivotStockValues($center->id, $productCategory->id);
            $pivotEmpty = $pivotValues['empty'];
            $pivotFilled = $pivotValues['filled'];

            // Get actual values (calculated from bottles table)
            $actualValues = $this->getActualStockValues($center->id, $productCategory->id);
            $actualEmpty = $actualValues['empty'];
            $actualFilled = $actualValues['filled'];

            // Calculate differences
            $emptyDiff = $actualEmpty - $pivotEmpty;
            $filledDiff = $actualFilled - $pivotFilled;

            // Format differences
            $emptyDiffFormatted = $this->formatDifference($emptyDiff);
            $filledDiffFormatted = $this->formatDifference($filledDiff);

            // Update totals
            $totalPivotEmpty += $pivotEmpty;
            $totalPivotFilled += $pivotFilled;
            $totalActualEmpty += $actualEmpty;
            $totalActualFilled += $actualFilled;

            // Display statistics for this product category
            $this->command->line("{$productCategory->name}:");
            $this->command->line("  - PIVOT   : {$pivotEmpty} empty, {$pivotFilled} filled");
            $this->command->line("  - BOTTLES : {$actualEmpty} empty {$emptyDiffFormatted}, {$actualFilled} filled {$filledDiffFormatted}");

            if ($emptyDiff !== 0 || $filledDiff !== 0) {
                $this->command->warn("  ⚠️ DIFFERENCE DETECTED for {$productCategory->name} in {$center->name}");
            }

            // If we have initial statistics, calculate the difference from the beginning
            $this->displayInitialDifference($center, $productCategory, $actualEmpty, $actualFilled);
        }

        // Display totals
        $this->displayTotalStatistics($totalPivotEmpty, $totalPivotFilled, $totalActualEmpty, $totalActualFilled, $center);
    }

    /**
     * Display difference from initial state
     */
    private function displayInitialDifference(DistributionCenter $center, ProductCategory $productCategory, int $actualEmpty, int $actualFilled): void
    {
        if (isset($this->initialStockStats[$center->id][$productCategory->id])) {
            $initialActualValues = $this->initialStockStats[$center->id][$productCategory->id]['actual'];
            $initialEmptyCount = $initialActualValues['empty'];
            $initialFilledCount = $initialActualValues['filled'];

            $totalEmptyDiff = $actualEmpty - $initialEmptyCount;
            $totalFilledDiff = $actualFilled - $initialFilledCount;

            if ($totalEmptyDiff !== 0 || $totalFilledDiff !== 0) {
                $emptyDiffText = $this->formatDifference($totalEmptyDiff);
                $filledDiffText = $this->formatDifference($totalFilledDiff);
                $this->command->line("  - TOTAL DIFF: {$emptyDiffText} empty, {$filledDiffText} filled since beginning");
            }
        }
    }

    /**
     * Display total statistics for a center
     */
    private function displayTotalStatistics(int $totalPivotEmpty, int $totalPivotFilled, int $totalActualEmpty, int $totalActualFilled, DistributionCenter $center): void
    {
        $totalEmptyDiff = $totalActualEmpty - $totalPivotEmpty;
        $totalFilledDiff = $totalActualFilled - $totalPivotFilled;
        $totalEmptyDiffFormatted = $this->formatDifference($totalEmptyDiff);
        $totalFilledDiffFormatted = $this->formatDifference($totalFilledDiff);

        $this->command->line('--------------------------------------------------');
        $this->command->line('TOTALS:');
        $this->command->line("  - PIVOT   : {$totalPivotEmpty} empty, {$totalPivotFilled} filled, Total: ".($totalPivotEmpty + $totalPivotFilled));
        $this->command->line("  - BOTTLES : {$totalActualEmpty} empty {$totalEmptyDiffFormatted}, {$totalActualFilled} filled {$totalFilledDiffFormatted}, Total: ".
            ($totalActualEmpty + $totalActualFilled).' '.$this->formatDifference($totalEmptyDiff + $totalFilledDiff));

        if ($totalEmptyDiff !== 0 || $totalFilledDiff !== 0) {
            $this->command->warn("  ⚠️ TOTAL DIFFERENCE DETECTED for {$center->name}");
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
            $confirmedOrders[] = $this->createRandomOrder($customers, $centers, OrderStatus::CONFIRMED());
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
            $processingOrders[] = $this->createRandomOrder(
                $customers,
                $centers,
                OrderStatus::PROCESSING(),
                $deliveryPersons->isNotEmpty() ? $deliveryPersons->random() : null
            );
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
                $order = $this->createRandomOrder(
                    $customers,
                    $centers,
                    OrderStatus::DELIVERED(),
                    $deliveryPerson,
                    true
                );

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
            $order = $this->createRandomOrder(
                $customers,
                $centers,
                OrderStatus::CANCELLED(),
                null,
                true
            );
            $cancelledOrders[] = $order;
        }

        $this->addOrderItems($cancelledOrders);
        $this->orderTypeStats['cancelled'] = count($cancelledOrders);

        $this->command->info(count($cancelledOrders).' cancelled orders created.');
    }

    /**
     * Create a random order with specified parameters
     */
    private function createRandomOrder(
        Collection $customers,
        Collection $centers,
        OrderStatus $status,
        ?DeliveryPerson $deliveryPerson = null,
        bool $addRatings = false
    ): Order {
        $customer = $customers->random();
        $center = $centers->random();
        $deliveryAddress = $customer->deliveryAddresses()->inRandomOrder()->first();

        $orderDate = now()->subDays(rand(1, 10));
        $confirmedAt = $orderDate->copy()->addMinutes(rand(5, 60));

        $orderData = [
            'customer_id' => $customer->id,
            'distribution_center_id' => $center->id,
            'delivery_address_id' => $deliveryAddress->id,
            'delivery_person_id' => $deliveryPerson?->id,
            'order_number' => 'ORD-'.rand(100000, 999999),
            'order_date' => $orderDate,
            'confirmed_at' => $confirmedAt,
        ];

        // For orders that are processing or delivered, set the processing_at timestamp
        if ($status->equals(OrderStatus::PROCESSING()) || $status->equals(OrderStatus::DELIVERED())) {
            $orderData['processing_at'] = $confirmedAt->copy()->addHours(rand(1, 5));
        }

        // For delivered orders, set the delivered_at timestamp and delivery_date
        if ($status->equals(OrderStatus::DELIVERED())) {
            $deliveredAt = $orderData['processing_at']->copy()->addHours(rand(1, 8));
            $orderData['delivered_at'] = $deliveredAt;
            $orderData['delivery_date'] = $deliveredAt;
        }

        if ($addRatings) {
            $orderData = array_merge($orderData, [
                'rating' => rand(0, 100) <= 70 ? rand(30, 50) / 10 : null,
                'comments' => rand(0, 100) <= 70 ? fake()->realText(150) : null,
                'center_comments' => rand(0, 100) <= 40 ? fake()->realText(100) : null,
            ]);
        }

        return Order::factory()
            ->state(['status' => $status]) // Explicitly set the status
            ->create($orderData);
    }

    /**
     * Add order items to orders
     */
    private function addOrderItems(array $orders): void
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

    /**
     * Add bottles to an order
     */
    private function addBottlesToOrder(Order $order): void
    {
        // Get bottle types with available stock
        $bottleStocks = $this->getBottleTypesWithStock($order->distributionCenter);

        if (empty($bottleStocks)) {
            $this->command->warn("No filled bottles available for order {$order->order_number}");

            return;
        }

        // Select a random subset of bottle types
        $selectedTypes = $this->selectRandomBottleTypes($bottleStocks);

        foreach ($selectedTypes as $bottleType) {
            // Determine bottle order type (full or recharge)
            $bottleOrderType = rand(1, 100) <= 40
                ? BottleOrderType::FULL()
                : BottleOrderType::RECHARGE();

            // Calculate available quantity
            $maxQuantity = min($bottleType->pivot->stock_filled, 3);
            $quantity = rand(1, $maxQuantity);

            // Create order item with bottles
            $this->createBottleOrderItem($order, $bottleType, $quantity, $bottleOrderType);

            // Track statistics
            $this->updateBottleTypeStatistics($bottleType, $order, $quantity, $bottleOrderType);
        }
    }

    /**
     * Select random bottle types from available stocks
     */
    private function selectRandomBottleTypes(array $bottleStocks): array
    {
        $count = min(rand(1, 3), count($bottleStocks));
        $keys = array_rand($bottleStocks, $count);

        if (! is_array($keys)) {
            $keys = [$keys]; // If only one key is returned, wrap it in an array
        }

        $selectedTypes = [];
        foreach ($keys as $key) {
            $selectedTypes[] = $bottleStocks[$key];
        }

        return $selectedTypes;
    }

    /**
     * Update statistics for bottle types
     */
    private function updateBottleTypeStatistics(
        ProductCategory $productCategory,
        Order $order,
        int $quantity,
        BottleOrderType $bottleOrderType
    ): void {
        if (! isset($this->bottleTypeStats[$productCategory->id])) {
            $this->bottleTypeStats[$productCategory->id] = [
                'name' => $productCategory->name,
                'total_orders' => 0,
                'total_bottles' => 0,
                'by_status' => [],
                'by_order_type' => [],
            ];
        }

        $this->bottleTypeStats[$productCategory->id]['total_orders']++;
        $this->bottleTypeStats[$productCategory->id]['total_bottles'] += $quantity;

        // By order status
        if (! isset($this->bottleTypeStats[$productCategory->id]['by_status'][$order->status->value])) {
            $this->bottleTypeStats[$productCategory->id]['by_status'][$order->status->value] = 0;
        }
        $this->bottleTypeStats[$productCategory->id]['by_status'][$order->status->value] += $quantity;

        // By bottle order type
        if (! isset($this->bottleTypeStats[$productCategory->id]['by_order_type'][$bottleOrderType->value])) {
            $this->bottleTypeStats[$productCategory->id]['by_order_type'][$bottleOrderType->value] = 0;
        }
        $this->bottleTypeStats[$productCategory->id]['by_order_type'][$bottleOrderType->value] += $quantity;
    }

    /**
     * Add accessories to an order
     */
    private function addAccessoriesToOrder(Order $order): void
    {
        // Get accessories available in the distribution center that aren't sold yet
        $accessories = Accessory::where('distribution_center_id', $order->distribution_center_id)
            ->where('is_sold', false)
            ->get();

        if ($accessories->isEmpty()) {
            return;
        }

        // Group accessories by type and select random types
        $accessoriesByType = $accessories->groupBy(function ($accessory) {
            return $accessory->accessoryTypeId;
        });

        $selectedTypes = $accessoriesByType->random(min(rand(1, 2), $accessoriesByType->count()));

        foreach ($selectedTypes as $accessoriesOfSameType) {
            // Determine quantity (1-3) of accessories to sell, but limit by what's available
            $maxQty = min(3, $accessoriesOfSameType->count());
            $quantity = rand(1, $maxQty);

            // Get the specific accessories to include
            $selectedAccessories = $accessoriesOfSameType->take($quantity);
            $firstAccessory = $selectedAccessories->first();

            // Create order item for these accessories
            $this->createAccessoryOrderItem($order, $firstAccessory, $selectedAccessories);
        }
    }

    /**
     * Create a bottle order item
     */
    private function createBottleOrderItem(Order $order, ProductCategory $productCategory, int $quantity, BottleOrderType $bottleOrderType): void
    {
        $bottleType = $productCategory->typeInstance;
        $bottleWithContentPrice = $bottleType->full_price ?? 5000;
        $contentPrice = $bottleType->content_price ?? 3500;

        $unitPrice = match ($bottleOrderType) {
            BottleOrderType::FULL() => $bottleWithContentPrice,
            BottleOrderType::RECHARGE() => $contentPrice,
        };

        // Determine bottle status based on order status
        $targetBottleStatus = match (true) {
            $order->status->equals(OrderStatus::PROCESSING()) => BottleStatus::WITH_DELIVERY_PERSON(),
            $order->status->equals(OrderStatus::CONFIRMED()) => BottleStatus::IN_STOCK(), // Remains in stock until delivery person takes it
            $order->status->equals(OrderStatus::DELIVERED()) => BottleStatus::WITH_CLIENT(),
            $order->status->equals(OrderStatus::CANCELLED()) => BottleStatus::IN_STOCK(),
            default => BottleStatus::IN_STOCK(),
        };

        $availableBottles = Bottle::whereHas('product', function ($query) use ($productCategory) {
            $query->where('product_category_id', $productCategory->id);
        })
            ->where('distribution_center_id', $order->distribution_center_id)
            ->where('is_filled', true)
            ->where('status', BottleStatus::IN_STOCK())
            ->take($quantity)
            ->get();

        $foundCount = $availableBottles->count();

        if ($foundCount === 0) {
            return;
        }

        if ($foundCount < $quantity) {
            Log::info('Insufficient stock: missing '.($quantity - $foundCount)." bottles of type {$productCategory->name} for order #{$order->order_number}");
        }

        // Create a single OrderItem for all bottles of the same type and option
        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $productCategory->id,
            'quantity' => $foundCount,
            'bottle_type' => $bottleOrderType->value,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $foundCount,
        ]);

        // Update the status of each bottle individually
        foreach ($availableBottles as $bottle) {
            $this->processBottle($bottle, $order, $orderItem, $targetBottleStatus);
        }
    }

    /**
     * Process an individual bottle for an order
     */
    private function processBottle(Bottle $bottle, Order $order, OrderItem $orderItem, BottleStatus $targetBottleStatus): void
    {
        $oldStatus = $bottle->status;

        // Update the status
        $bottle->update(['status' => $targetBottleStatus]);

        // Track status changes
        $statusKey = $oldStatus->value.' -> '.$targetBottleStatus->value;
        if (! isset($this->bottleStatusCounts[$statusKey])) {
            $this->bottleStatusCounts[$statusKey] = 0;
        }
        $this->bottleStatusCounts[$statusKey]++;

        // Record appropriate movement
        $this->createBottleMovement($bottle, $order, $targetBottleStatus);

        // Update pivot stock counts
        $this->updatePivotStockCountsAfterStatusChange($bottle);

        // Associate bottle with OrderItem only for orders beyond confirmed status
        if (! $order->status->equals(OrderStatus::CONFIRMED())) {
            OrderBottleScans::create([
                'order_item_id' => $orderItem->id,
                'bottle_id' => $bottle->id,
            ]);
        }
    }

    /**
     * Create an accessory order item
     */
    private function createAccessoryOrderItem(Order $order, Accessory $accessory, $selectedAccessories): void
    {
        // Find the product category for this accessory type
        $productCategory = ProductCategory::where('id', $accessory->product_category_id)
            ->first();

        if (! $productCategory) {
            Log::warning("Product category not found for accessory #{$accessory->id} with category ID {$accessory->product_category_id}");

            return;
        }

        $quantity = count($selectedAccessories);

        // Get the price from the accessory type
        $accessoryType = $accessory->accessoryType;
        if (! $accessoryType) {
            Log::warning("Accessory type not found for accessory #{$accessory->id}");

            return;
        }

        // Create or update order item
        $existingItem = $order->items()
            ->where('product_category_id', $productCategory->id)
            ->first();

        if ($existingItem) {
            // If item already exists, increase quantity and price
            $newQuantity = $existingItem->quantity + $quantity;
            $newPrice = $accessoryType->price * $newQuantity;

            $existingItem->update([
                'quantity' => $newQuantity,
                'total_price' => $newPrice,
            ]);

            $orderItem = $existingItem;
        } else {
            // Create new order item
            $orderItem = OrderItem::create([
                'order_id' => $order->id,
                'product_category_id' => $productCategory->id,
                'quantity' => $quantity,
                'bottle_type' => null,
                'unit_price' => $accessoryType->price,
                'total_price' => $accessoryType->price * $quantity,
            ]);
        }

        // Mark each selected accessory as sold (unless order was cancelled)
        if (! $order->status->equals(OrderStatus::CANCELLED())) {
            foreach ($selectedAccessories as $accessory) {
                $accessory->update([
                    'is_sold' => true,
                ]);

                // Mise à jour du stock sans passer productCategory
                $this->updateAccessoryStockCount($accessory);
            }
        }

        // Update pivot table stock counts based on order status
        if ($order->status->equals(OrderStatus::CANCELLED())) {
            Log::info("Order {$order->order_number} was cancelled - not updating accessory stock");
        }
    }

    /**
     * Update order totals
     */
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
        // Generate a payment reference based on payment method
        $paymentMethods = PaymentMethod::cases();
        $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

        $paymentReference = $this->generatePaymentReference($paymentMethod, $order->order_date);
        $paymentStatus = PaymentStatus::PAID();

        // Determine payment date
        $paymentDate = match ($paymentStatus->value) {
            'paid' => $order->order_date,
            'pending', 'failed' => null,
            default => null,
        };

        // Create payment record
        OrderPayment::create([
            'order_id' => $order->id,
            'payment_reference' => $paymentReference,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'amount_paid' => $paymentStatus == PaymentStatus::PAID() ? $totalAmount : 0,
            'amount_due' => $paymentStatus == PaymentStatus::PAID() ? 0 : $totalAmount,
            'payment_date' => $paymentDate,
            'payment_notes' => null,
        ]);

        // If the order is cancelled and it was paid, create a refund
        if ($order->status->equals(OrderStatus::CANCELLED()) && $paymentStatus == PaymentStatus::PAID()) {
            $this->createRefundForCancelledOrder($order, $totalAmount);
        }
    }

    /**
     * Generate payment reference based on payment method
     */
    private function generatePaymentReference(PaymentMethod $paymentMethod, $paymentDate): string
    {
        return match ($paymentMethod) {
            PaymentMethod::ORANGE_MONEY() => $this->generateOrangeMoneyReference($paymentDate),
            PaymentMethod::MTN_MONEY() => $this->generateMobileMoneyReference(),
            PaymentMethod::CREDIT_CARD() => $this->generateCreditCardReference(),
            default => 'PAY-'.strtoupper(substr(md5(uniqid()), 0, 10))
        };
    }

    /**
     * Generate Orange Money reference
     * Format: PP250620.X.YAZ
     * where 25 = year (2025), 06 = month (June), 20 = day
     * X is a number between 1000-9999
     * Y is uppercase French alphabet letters
     * Z is a number between 10000-99999
     */
    private function generateOrangeMoneyReference($paymentDate): string
    {
        $dateFormat = $paymentDate->format('ymd'); // Example: 250620
        $randomNumber = rand(1000, 9999); // X part

        // Generate Y part (random uppercase French letter)
        $frenchAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomLetter = $frenchAlphabet[rand(0, strlen($frenchAlphabet) - 1)];

        $randomDigits = rand(10000, 99999); // Z part

        return "PP{$dateFormat}.{$randomNumber}.{$randomLetter}{$randomDigits}";
    }

    /**
     * Generate Mobile Money reference
     * Format: Long number (11 digits)
     */
    private function generateMobileMoneyReference(): string
    {
        // Generate a random 11-digit number
        return (string) rand(10000000000, 99999999999);
    }

    /**
     * Generate Credit Card reference
     * Format: VISA-PUR @ X-Y
     * where X is an 8-digit number
     * and Y is a 15-digit number
     */
    private function generateCreditCardReference(): string
    {
        $firstNumber = rand(10000000, 99999999); // X part (8 digits)
        $secondNumber = rand(100000000000000, 999999999999999); // Y part (15 digits)

        return "VISA-PUR @ {$firstNumber}-{$secondNumber}";
    }

    /**
     * Create a bottle movement record when status changes
     */
    private function createBottleMovement(Bottle $bottle, Order $order, BottleStatus $targetStatus): void
    {
        // Determine movement type based on target status
        $movementType = match ($targetStatus) {
            BottleStatus::WITH_DELIVERY_PERSON() => BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
            BottleStatus::WITH_CLIENT() => BottleMovementType::DELIVERY_TO_CUSTOMER(),
            default => null
        };

        if (! $movementType) {
            Log::warning("Movement type not supported for status {$targetStatus->value}");

            return;
        }

        // Determine movement parameters
        $params = [
            'bottle_id' => $bottle->id,
            'distribution_center_id' => $order->distribution_center_id,
            'type' => $movementType,
            'created_at' => $order->updated_at ?? now()->subHours(rand(1, 24)),
        ];

        $this->setMovementUserParams($params, $order, $targetStatus);

        // For delivered orders, if bottle was in stock, record transition through delivery person first
        if ($targetStatus === BottleStatus::WITH_CLIENT() && $bottle->getOriginal('status') === BottleStatus::IN_STOCK()->value) {
            $this->createIntermediateMovement($bottle, $order);
        }

        // Create movement record
        BottleMovement::create($params);
    }

    /**
     * Set user-related parameters for movement record
     */
    private function setMovementUserParams(array &$params, Order $order, BottleStatus $targetStatus): void
    {
        if ($targetStatus === BottleStatus::WITH_DELIVERY_PERSON()) {
            $params['user_id'] = $order->delivery_person_id ?? User::role('center_manager')->inRandomOrder()->first()->id;
            $params['delivery_person_id'] = $order->delivery_person_id;
            $params['notes'] = "Bottle assigned to delivery person for order #{$order->order_number}";
        } elseif ($targetStatus === BottleStatus::WITH_CLIENT()) {
            $params['user_id'] = $order->delivery_person_id ?? User::role('center_manager')->inRandomOrder()->first()->id;
            $params['delivery_person_id'] = $order->delivery_person_id;
            $params['customer_id'] = $order->customer_id;
            $params['notes'] = "Bottle delivered to customer via order #{$order->order_number}";
        }
    }

    /**
     * Create intermediate movement record for bottles going directly from stock to client
     */
    private function createIntermediateMovement(Bottle $bottle, Order $order): void
    {
        BottleMovement::create([
            'bottle_id' => $bottle->id,
            'user_id' => $order->delivery_person_id ?? User::role('center_manager')->inRandomOrder()->first()->id,
            'delivery_person_id' => $order->delivery_person_id,
            'distribution_center_id' => $order->distribution_center_id,
            'type' => BottleMovementType::ASSIGNMENT_TO_DELIVERY(),
            'notes' => "Bottle assigned to delivery person for order #{$order->order_number}",
            'created_at' => now()->subHours(rand(24, 48)),
        ]);

        // Track intermediate movements
        $intermediateKey = 'IN_STOCK -> WITH_DELIVERY_PERSON (intermediate)';
        if (! isset($this->bottleStatusCounts[$intermediateKey])) {
            $this->bottleStatusCounts[$intermediateKey] = 0;
        }
        $this->bottleStatusCounts[$intermediateKey]++;
    }

    /**
     * Update stock counters in the pivot table after a status change
     * This step is crucial to maintain consistency between actual bottle count
     * and values in product_category_distribution_center pivot table
     */
    private function updatePivotStockCountsAfterStatusChange(Bottle $bottle): void
    {
        // If the bottle leaves the stock, decrement the counter in the pivot
        if ($bottle->status !== BottleStatus::IN_STOCK()) {
            // Find product category for this bottle type
            $productCategory = ProductCategory::where('id', $bottle->product_category_id)->first();

            if (! $productCategory) {
                Log::warning("Product category not found for bottle #{$bottle->id} with category ID {$bottle->product_category_id}");

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

            // Update stock count based on bottle fill status
            $this->updateStockPivotCount($bottle, $productCategory, $pivotData);
        }
        // If the bottle returns to stock, we would need to increment the counter
        // This case is not handled here as we don't return bottles to stock in this seeder
    }

    /**
     * Update stock count in pivot table
     */
    private function updateStockPivotCount(Bottle $bottle, ProductCategory $productCategory, $pivotData): void
    {
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

    /**
     * Create refund record for a cancelled order
     */
    private function createRefundForCancelledOrder(Order $order, float $totalAmount): void
    {
        $refundAmount = $totalAmount;
        $initiatedBy = $this->getRandomAdminUserId();

        $orderDate = $order->order_date;
        $cancelledAt = $orderDate->copy()->addHours(rand(1, 72));
        $cancelledAt = $cancelledAt->min(now()->subHours(1));

        $order->update([
            'cancelled_at' => $cancelledAt,
        ]);

        $completedAt = $cancelledAt->copy()->addHours(rand(1, 24));
        $completedAt = $completedAt->min(now());

        Refund::create([
            'order_id' => $order->id,
            'initiated_by' => $initiatedBy,
            'refund_method' => PaymentMethod::cases()[array_rand(PaymentMethod::cases())],
            'refund_identifier' => 'REF-'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'status' => PaymentStatus::PAID(),
            'amount' => $refundAmount,
            'reason' => 'Order cancelled by customer',
            'notes' => 'Automatic refund after cancellation',
            'initiated_at' => $cancelledAt,
            'completed_at' => $completedAt,
        ]);

        $order->cancelled_by = $initiatedBy;
        $order->cancelled_reason = rand(0, 1) ? 'Customer request' : 'Technical problem at the center';
        $order->save();

        Log::info("Refund created for order #{$order->order_number} in the amount of {$refundAmount} ".Currency::make(config('countries.default_currency', 'XAF'))->label);
    }

    /**
     * Get random admin user ID for refund
     */
    private function getRandomAdminUserId(): int
    {
        return User::role('admin')->inRandomOrder()->first()?->id
            ?? User::role('center_manager')->inRandomOrder()->first()?->id
            ?? 1;
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
            ->where('pcdc.distribution_center_id', $center->id)
            ->where('pc.product_type', ProductType::BOTTLE()->value)
            ->where('pcdc.stock_filled', '>', 0)
            ->select('pc.*', 'pcdc.stock_filled', 'pcdc.stock_empty')
            ->get();

        // Format the data to mimic the previous bottleTypeStocks relation
        $result = [];
        foreach ($stockData as $item) {
            $productCategory = ProductCategory::find($item->id);
            if ($productCategory) {
                // Add pivot data to the product category
                $productCategory->pivot = (object) [
                    'stock_filled' => $item->stock_filled,
                    'stock_empty' => $item->stock_empty,
                ];
                $result[] = $productCategory;
            }
        }

        return $result;
    }

    /**
     * Update accessory stock count in the pivot table
     */
    private function updateAccessoryStockCount(Accessory $accessory): void
    {
        $productCategory = ProductCategory::where('product_type', ProductType::ACCESSORY())
            ->where('id', $accessory->product_category_id)
            ->first();

        if (! $productCategory) {
            Log::warning("Product category not found for accessory #{$accessory->id} with category ID {$accessory->product_category_id}");

            return;
        }

        // Get current pivot data
        $pivotData = DB::table('product_category_distribution_center')
            ->where('distribution_center_id', $accessory->distribution_center_id)
            ->where('product_category_id', $productCategory->id)
            ->first();

        if (! $pivotData) {
            Log::warning("Pivot data not found for accessory #{$accessory->id} in center {$accessory->distribution_center_id}");

            return;
        }

        // Decrement stock count by 1
        $newStock = max(0, $pivotData->stock - 1);

        DB::table('product_category_distribution_center')
            ->where('distribution_center_id', $accessory->distribution_center_id)
            ->where('product_category_id', $productCategory->id)
            ->update([
                'stock' => $newStock,
                'updated_at' => now(),
            ]);
    }

    /**
     * Create notifications for a subset of recent orders.
     */
    private function createNotificationsForOrders(): void
    {
        $this->command->info('Creating notifications for recent orders...');

        $usersToNotify = User::role(UserRole::SUPER_ADMIN())->get();
        $recentOrders = Order::latest()->take(5)->get();

        if ($usersToNotify->isEmpty()) {
            $this->command->error('No users found for notifications.');

            return;
        }

        if ($recentOrders->isEmpty()) {
            $this->command->info('No recent orders found to create notifications for.');

            return;
        }

        foreach ($recentOrders as $order) {
            $recipientsArray = $usersToNotify->all();

            // Vérifier si le manager existe avant de l'ajouter
            if ($order->distributionCenter && $order->distributionCenter->manager) {
                $recipientsArray[] = $order->distributionCenter->manager;
            }

            FacadesNotification::send($recipientsArray, new OrderNotification($order, NotificationType::ORDER_CREATED()));
        }

        $this->command->info($recentOrders->count().' notifications created successfully');
    }

    /**
     * Create specific test orders for customer1@test.com with varied data
     */
    private function createTestOrdersForCustomer1(): void
    {
        $this->command->info('Creating test orders for customer1...');

        // Find customer1
        $customer1User = User::where('email', 'customer1@test.com')->first();
        if (! $customer1User || ! $customer1User->customer) {
            $this->command->error('Customer1 not found. Run UserSeeder first.');

            return;
        }

        // Clean existing test orders for customer1
        $customer1 = $customer1User->customer;
        $existingTestOrders = Order::where('customer_id', $customer1->id)
            ->where('order_number', 'LIKE', 'TEST-C'.$customer1->id.'-%')
            ->get();

        foreach ($existingTestOrders as $order) {
            // Delete related records first
            $order->items()->delete();
            $order->payment()->delete();
            \App\Models\Refund::where('order_id', $order->id)->delete();
            $order->delete();
        }

        $this->command->info('Cleaned '.count($existingTestOrders).' existing test orders for customer1.');

        $centers = DistributionCenter::all();
        $deliveryPersons = DeliveryPerson::where('is_active', true)->get();

        if ($centers->isEmpty()) {
            $this->command->error('No distribution centers found.');

            return;
        }

        // Define order scenarios with varied data
        $orderScenarios = [
            // Pending orders (3)
            ['status' => OrderStatus::PENDING(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::ORANGE_MONEY(), 'payment_status' => PaymentStatus::PENDING(), 'days_ago' => 1],
            ['status' => OrderStatus::PENDING(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::MTN_MONEY(), 'payment_status' => PaymentStatus::PENDING(), 'days_ago' => 2],
            ['status' => OrderStatus::PENDING(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::CREDIT_CARD(), 'payment_status' => PaymentStatus::FAILED(), 'days_ago' => 3],

            // Confirmed orders (4)
            ['status' => OrderStatus::CONFIRMED(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::ORANGE_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 4],
            ['status' => OrderStatus::CONFIRMED(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::MTN_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 5],
            ['status' => OrderStatus::CONFIRMED(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::CREDIT_CARD(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 6],
            ['status' => OrderStatus::CONFIRMED(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::ORANGE_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 7],

            // Processing orders (4)
            ['status' => OrderStatus::PROCESSING(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::MTN_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 8, 'needs_delivery_person' => true],
            ['status' => OrderStatus::PROCESSING(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::CREDIT_CARD(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 9, 'needs_delivery_person' => true],
            ['status' => OrderStatus::PROCESSING(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::ORANGE_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 10, 'needs_delivery_person' => true],
            ['status' => OrderStatus::PROCESSING(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::MTN_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 11, 'needs_delivery_person' => true],

            // Delivered orders (4)
            ['status' => OrderStatus::DELIVERED(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::CREDIT_CARD(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 15, 'needs_delivery_person' => true, 'delivered' => true, 'rating' => 4.5, 'comment' => 'Excellent service!'],
            ['status' => OrderStatus::DELIVERED(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::ORANGE_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 20, 'needs_delivery_person' => true, 'delivered' => true, 'rating' => 5.0, 'comment' => 'Perfect delivery'],
            ['status' => OrderStatus::DELIVERED(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::MTN_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 25, 'needs_delivery_person' => true, 'delivered' => true, 'rating' => 3.5],
            ['status' => OrderStatus::DELIVERED(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::CREDIT_CARD(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 30, 'needs_delivery_person' => true, 'delivered' => true, 'rating' => 4.0, 'comment' => 'Good service, on time'],

            // Cancelled orders (2)
            ['status' => OrderStatus::CANCELLED(), 'delivery_type' => \App\Enums\DeliveryType::NORMAL(), 'payment_method' => PaymentMethod::ORANGE_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 12, 'cancelled' => true],
            ['status' => OrderStatus::CANCELLED(), 'delivery_type' => \App\Enums\DeliveryType::FAST(), 'payment_method' => PaymentMethod::MTN_MONEY(), 'payment_status' => PaymentStatus::PAID(), 'days_ago' => 18, 'cancelled' => true],
        ];

        $createdOrders = [];
        foreach ($orderScenarios as $index => $scenario) {
            $order = $this->createCustomer1Order($customer1, $centers, $deliveryPersons, $scenario, $index + 1);
            if ($order) {
                $createdOrders[] = $order;
            }
        }

        $this->command->info(count($createdOrders).' test orders created for customer1.');
    }

    /**
     * Create a specific order for customer1
     */
    private function createCustomer1Order(Customer $customer, Collection $centers, Collection $deliveryPersons, array $scenario, int $orderNumber): ?Order
    {
        $center = $centers->random();
        $deliveryAddress = $customer->deliveryAddresses()->inRandomOrder()->first();

        if (! $deliveryAddress) {
            $this->command->error('No delivery addresses found for customer1.');

            return null;
        }

        $orderDate = now()->subDays($scenario['days_ago']);
        $confirmedAt = $orderDate->copy()->addMinutes(rand(5, 60));

        $orderData = [
            'customer_id' => $customer->id,
            'distribution_center_id' => $center->id,
            'delivery_address_id' => $deliveryAddress->id,
            'order_number' => 'TEST-C'.$customer->id.'-'.str_pad($orderNumber, 3, '0', STR_PAD_LEFT),
            'order_date' => $orderDate,
            'delivery_type' => $scenario['delivery_type'],
            'status' => $scenario['status'],
            'subtotal' => 0, // Will be updated after adding items
            'delivery_fee' => $scenario['delivery_type']->fee(),
            'total_amount' => $scenario['delivery_type']->fee(),
        ];

        // Set delivery person for orders that need one
        if (isset($scenario['needs_delivery_person']) && $deliveryPersons->isNotEmpty()) {
            $orderData['delivery_person_id'] = $deliveryPersons->random()->id;
        }

        // Set timestamps based on status
        if ($scenario['status']->equals(OrderStatus::CONFIRMED()) ||
            $scenario['status']->equals(OrderStatus::PROCESSING()) ||
            $scenario['status']->equals(OrderStatus::DELIVERED())) {
            $orderData['confirmed_at'] = $confirmedAt;
        }

        if ($scenario['status']->equals(OrderStatus::PROCESSING()) ||
            $scenario['status']->equals(OrderStatus::DELIVERED())) {
            $orderData['processing_at'] = $confirmedAt->copy()->addHours(rand(1, 5));
        }

        if (isset($scenario['delivered']) && $scenario['delivered']) {
            $orderData['delivered_at'] = $orderData['processing_at']->copy()->addHours(rand(1, 8));
            $orderData['delivery_date'] = $orderData['delivered_at'];
        }

        if (isset($scenario['cancelled']) && $scenario['cancelled']) {
            $orderData['cancelled_at'] = $confirmedAt->copy()->addHours(rand(1, 24));
            $orderData['cancelled_reason'] = 'Customer request for cancellation';
        }

        // Add ratings and comments
        if (isset($scenario['rating'])) {
            $orderData['rating'] = $scenario['rating'];
        }
        if (isset($scenario['comment'])) {
            $orderData['comments'] = $scenario['comment'];
        }

        $order = Order::create($orderData);

        // Create order items
        $this->addItemsToCustomer1Order($order);

        // Calculate totals
        $this->updateOrderTotals($order);

        // Create payment with specific method and status
        $this->createCustomer1Payment($order, $scenario);

        return $order;
    }

    /**
     * Add items to customer1's order
     */
    private function addItemsToCustomer1Order(Order $order): void
    {
        // Get available product categories for bottles
        $bottleCategories = ProductCategory::bottles()
            ->whereHas('distributionCenters', function ($query) use ($order) {
                $query->where('distribution_center_id', $order->distribution_center_id)
                    ->where('stock_filled', '>', 0);
            })
            ->get();

        if ($bottleCategories->isEmpty()) {
            // If no bottles available, just create a basic item
            $basicCategory = ProductCategory::bottles()->first();
            if ($basicCategory) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_category_id' => $basicCategory->id,
                    'quantity' => rand(1, 3),
                    'bottle_type' => \App\Enums\BottleOrderType::FULL()->value,
                    'unit_price' => 5000,
                    'total_price' => 5000 * rand(1, 3),
                ]);
            }

            return;
        }

        // Add 1-2 different bottle types
        $selectedCategories = $bottleCategories->take(rand(1, 2));

        foreach ($selectedCategories as $category) {
            $quantity = rand(1, 3);
            $bottleType = rand(0, 1) ? \App\Enums\BottleOrderType::FULL() : \App\Enums\BottleOrderType::RECHARGE();
            $unitPrice = $bottleType->equals(\App\Enums\BottleOrderType::FULL()) ? 5000 : 3500;

            OrderItem::create([
                'order_id' => $order->id,
                'product_category_id' => $category->id,
                'quantity' => $quantity,
                'bottle_type' => $bottleType->value,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice * $quantity,
            ]);
        }
    }

    /**
     * Create payment for customer1's order with specific method and status
     */
    private function createCustomer1Payment(Order $order, array $scenario): void
    {
        $paymentMethod = $scenario['payment_method'];
        $paymentStatus = $scenario['payment_status'];
        $totalAmount = $order->total_amount;

        $paymentDate = $paymentStatus->equals(PaymentStatus::PAID()) ? $order->order_date : null;
        $amountPaid = $paymentStatus->equals(PaymentStatus::PAID()) ? $totalAmount : 0;
        $amountDue = $paymentStatus->equals(PaymentStatus::PAID()) ? 0 : $totalAmount;

        OrderPayment::create([
            'order_id' => $order->id,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'amount_paid' => $amountPaid,
            'amount_due' => $amountDue,
            'payment_reference' => $this->generatePaymentReference($paymentMethod, $order->order_date),
            'payment_date' => $paymentDate,
            'payment_notes' => 'Test payment for customer1',
        ]);

        // Create refund for cancelled paid orders
        if ($order->status->equals(OrderStatus::CANCELLED()) && $paymentStatus->equals(PaymentStatus::PAID())) {
            // Check if refund already exists
            $existingRefund = \App\Models\Refund::where('order_id', $order->id)->first();
            if (! $existingRefund) {
                $this->createRefundForCancelledOrder($order, $totalAmount);
            }
        }
    }
}
