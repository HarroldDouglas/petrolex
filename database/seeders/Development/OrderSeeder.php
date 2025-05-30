<?php

// database/seeders/Development/OrderSeeder.php

namespace Database\Seeders\Development;

use App\Enums\BottleOrderType;
use App\Enums\BottleStatus;
use App\Enums\DeliveryType;
use App\Models\Accessory;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\Customer;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
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
                ]);
        }

        $this->addOrderItems($cancelledOrders);

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
        $bottleStocks = $order->distributionCenter
            ->bottleTypeStocks()
            ->wherePivot('stock_filled', '>', 0)
            ->get();

        if ($bottleStocks->isEmpty()) {
            $this->command->warn("No filled bottles available for order {$order->order_number}");

            return;
        }

        $selectedTypes = $bottleStocks->random(min(rand(1, 3), $bottleStocks->count()));

        foreach ($selectedTypes as $bottleType) {
            $bottleOrderType = rand(1, 100) <= 40
                ? BottleOrderType::BOTTLE_WITH_CONTENT()
                : BottleOrderType::CONTENT();

            $maxQuantity = min($bottleType->pivot->stock_filled, 3);
            $quantity = rand(1, $maxQuantity);

            $this->createBottleOrderItem($order, $bottleType, $quantity, $bottleOrderType);
        }
    }

    private function addAccessoriesToOrder(Order $order): void
    {
        $accessories = Accessory::whereHas('product')
            ->where('distribution_center_id', $order->distribution_center_id)
            ->where('quantity', '>', 0)
            ->with(['product', 'accessoryType'])
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
            BottleOrderType::BOTTLE_WITH_CONTENT() => $bottleType->bottle_with_content_price,
            BottleOrderType::CONTENT() => $bottleType->content_price,
        };

        // Find available bottles (IN_STOCK + not linked to active order)
        $availableBottles = Bottle::where('bottle_type_id', $bottleType->id)
            ->where('distribution_center_id', $order->distribution_center_id)
            ->where('is_filled', true)
            ->where('status', BottleStatus::IN_STOCK())
            ->whereDoesntHave('product.orderItems', function ($query) {
                $query->whereHas('order', function ($orderQuery) {
                    $orderQuery->whereNotIn('status', ['cancelled', 'delivered']);
                });
            })
            ->with('product')
            ->take($quantity)
            ->get();

        $foundCount = $availableBottles->count();
        $missingCount = $quantity - $foundCount;

        // Determine bottle status based on order status
        $bottleStatus = match ($order->status->value) {
            'processing' => BottleStatus::WITH_DELIVERY_PERSON(),
            'confirmed' => BottleStatus::IN_STOCK(),
            'delivered' => BottleStatus::WITH_CLIENT(),
            'cancelled' => BottleStatus::IN_STOCK(),
            default => BottleStatus::IN_STOCK(),
        };

        // Add found bottles to order
        foreach ($availableBottles as $bottle) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $bottle->product_id,
                'quantity' => 1,
                'bottle_type' => $bottleOrderType->value,
                'unit_price' => $unitPrice,
                'total_price' => $unitPrice,
            ]);

            // Update bottle status according to order
            $bottle->update(['status' => $bottleStatus]);
        }

        // Generate missing bottles using ProductFactory
        if ($missingCount > 0) {
            for ($i = 0; $i < $missingCount; $i++) {
                $product = Product::factory()->bottle(
                    $bottleType->id,
                    $order->distribution_center_id,
                    [
                        'barcode' => 'BT'.strtoupper(Str::random(8)),
                        'is_filled' => true,
                        'status' => $bottleStatus,
                    ]
                )->create();

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'bottle_type' => $bottleOrderType->value,
                    'unit_price' => $unitPrice,
                    'total_price' => $unitPrice,
                ]);
            }

            $this->command->info("Generated {$missingCount} bottles of type {$bottleType->name} to complete the order");
        }
    }

    private function createAccessoryOrderItem(Order $order, Accessory $accessory, int $quantity): void
    {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $accessory->product_id,
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
        $deliveryFee = $order->delivery_type == DeliveryType::FAST() ? 1000 : 0;

        $order->update([
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $subtotal + $deliveryFee,
        ]);
    }
}
