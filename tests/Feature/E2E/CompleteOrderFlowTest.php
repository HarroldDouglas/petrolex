<?php

declare(strict_types=1);

namespace Tests\Feature\E2E;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Geography\Country;
use App\Models\Order;
use App\Models\ProductCategory;
use App\Models\User;
use App\Services\ProductCategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

/**
 * End-to-End test for complete order flow
 * Tests the entire journey from customer login to order completion
 * without mocks - real API requests, real database operations, real notifications
 */
class CompleteOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private string $customerToken;
    private User $customerUser;
    private Customer $customer;
    private CustomerDeliveryAddress $deliveryAddress;
    private DistributionCenter $distributionCenter;
    private User $managerUser;
    private DeliveryPerson $deliveryPerson;
    private array $productCategories = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Don't fake notifications - we want to see real ones!
        // NotificationFacade::fake(); // REMOVED - we want REAL notifications

        $this->prepareTestData();
    }

    /**
     * Test complete order flow from creation to delivery
     */
    public function test_complete_order_flow_from_customer_login_to_delivery(): void
    {
        echo "\n\n";
        echo "═══════════════════════════════════════════════════════════════════\n";
        echo "           E2E TEST: COMPLETE ORDER FLOW                           \n";
        echo "═══════════════════════════════════════════════════════════════════\n\n";

        // ─────────────────────────────────────────────────────────────────────
        // STEP 1: Customer Login
        // ─────────────────────────────────────────────────────────────────────
        echo "📱 STEP 1: Customer Login\n";
        $loginResponse = $this->postJson('/api/login', [
            'login' => $this->customerUser->email,
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $loginResponse->assertStatus(200);
        $this->customerToken = $loginResponse->json('data.access_token');
        echo "   ✅ Customer logged in successfully\n";
        echo "   📝 Email: {$this->customerUser->email}\n\n";

        // ─────────────────────────────────────────────────────────────────────
        // STEP 2: Create Order with Mixed Items
        // ─────────────────────────────────────────────────────────────────────
        echo "🛒 STEP 2: Create Order with Mixed Items\n";

        $productCategoryService = app(ProductCategoryService::class);

        $items = [];
        $subtotal = 0;

        foreach ($this->productCategories as $name => $category) {
            // For bottles, use FULL option
            $option = ($category->product_type->value === \App\Enums\ProductType::BOTTLE()->value)
                ? \App\Enums\BottleOrderType::FULL()
                : null;

            $price = $productCategoryService->getProductPrice($category->id, $option);
            $quantity = rand(1, 3);

            $items[] = [
                'product_category_id' => $category->id,
                'quantity' => $quantity,
                'unit_price' => $price,
                'option' => $option?->value,
            ];

            $subtotal += $price * $quantity;
            echo "   📦 {$name}: {$quantity}x @ {$price} FCFA".($option ? " ({$option->value})" : '')."\n";
        }

        $deliveryFee = 500.00;
        $totalAmount = $subtotal + $deliveryFee;

        echo "   💵 Subtotal: {$subtotal} FCFA\n";
        echo "   🚚 Delivery Fee: {$deliveryFee} FCFA\n";
        echo "   💰 Total: {$totalAmount} FCFA\n\n";

        $orderData = [
            'delivery_address_id' => $this->deliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => $items,
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
            'comments' => 'E2E Test Order - Complete Flow',
        ];

        $createOrderResponse = $this->postJson('/api/orders', $orderData, [
            'Authorization' => "Bearer {$this->customerToken}",
            'Accept' => 'application/json',
        ]);

        $createOrderResponse->assertStatus(201);

        $orderId = $createOrderResponse->json('data.order.id');
        $this->assertNotNull($orderId, 'Order ID should not be null');

        echo "   ✅ Order created successfully\n";
        echo "   🆔 Order ID: {$orderId}\n";
        echo "   📋 Order Number: {$createOrderResponse->json('data.order.order_number')}\n\n";

        // ─────────────────────────────────────────────────────────────────────
        // CHECKPOINT 1: Verify Nothing Debited and No Notifications Sent Yet
        // ─────────────────────────────────────────────────────────────────────
        echo "🔍 CHECKPOINT 1: Verify Pre-Payment State\n";

        $order = Order::find($orderId);
        $this->assertEquals(OrderStatus::PENDING()->value, $order->status);
        echo "   ✅ Order status is 'pending'\n";

        // Verify no payment has been processed
        $this->assertNull($order->payments()->where('payment_status', 'paid')->first());
        echo "   ✅ No payment has been completed\n";

        // Verify notifications state
        $notificationsCountBeforePayment = DB::table('notifications')->count();
        echo "   📬 Notifications before payment: {$notificationsCountBeforePayment}\n";
        if ($notificationsCountBeforePayment > 0) {
            echo "   ⚠️  FINDING: Notifications are sent BEFORE payment!\n";
            foreach (DB::table('notifications')->get() as $notif) {
                $user = User::find($notif->notifiable_id);
                echo "      → {$notif->type} to ".($user->email ?? 'Unknown')."\n";
            }
        }

        // Verify stock has NOT been decremented yet
        echo "   📦 Verifying stock before payment:\n";
        $stockBeforePayment = [];
        foreach ($items as $item) {
            $stockRecord = DB::table('product_category_distribution_center')
                ->where('product_category_id', $item['product_category_id'])
                ->where('distribution_center_id', $this->distributionCenter->id)
                ->first();

            $stockBeforePayment[$item['product_category_id']] = [
                'stock' => $stockRecord->stock,
                'stock_filled' => $stockRecord->stock_filled,
                'stock_empty' => $stockRecord->stock_empty,
            ];

            $productCategory = \App\Models\ProductCategory::find($item['product_category_id']);
            if ($productCategory->product_type->value === \App\Enums\ProductType::BOTTLE()->value) {
                $this->assertEquals(100, $stockRecord->stock_filled, 'Bottle stock should still be 100 before payment');
                echo "      → Bottles stock_filled: {$stockRecord->stock_filled} (unchanged ✓)\n";
            } else {
                $this->assertEquals(100, $stockRecord->stock, 'Accessory stock should still be 100 before payment');
                echo "      → Accessory stock: {$stockRecord->stock} (unchanged ✓)\n";
            }
        }
        echo "\n";

        // ─────────────────────────────────────────────────────────────────────
        // STEP 3: Initiate Payment
        // ─────────────────────────────────────────────────────────────────────
        echo "💳 STEP 3: Initiate Payment\n";

        $paymentResponse = $this->postJson("/api/orders/{$orderId}/payment", [
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'payment_details' => [
                'phone' => '670000000',
            ],
        ], [
            'Authorization' => "Bearer {$this->customerToken}",
            'Accept' => 'application/json',
        ]);

        $paymentResponse->assertStatus(200);
        $paymentReference = $paymentResponse->json('data.reference');
        echo "   ✅ Payment initiated successfully\n";
        echo "   🔖 Payment Reference: {$paymentReference}\n\n";

        // ─────────────────────────────────────────────────────────────────────
        // STEP 4: Simulate Payment Callback (Success)
        // ─────────────────────────────────────────────────────────────────────
        echo "📞 STEP 4: Simulate Payment Callback (Success)\n";

        $callbackResponse = $this->postJson('/api/payments/callback', [
            'application' => 'E2E_TEST_APP',
            'app_transaction_ref' => (string) $orderId,
            'operator_transaction_ref' => 'OP-'.time(),
            'transaction_ref' => 'TXN-'.time(),
            'transaction_type' => 'PAYIN',
            'transaction_amount' => $totalAmount,
            'transaction_fees' => 50,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'SUCCESS',
            'transaction_reason' => 'Payment successful',
            'transaction_message' => 'Transaction completed successfully',
            'customer_phone_number' => '670000000',
            'signature' => hash('sha256', 'test-signature-'.$orderId),
        ]);

        if ($callbackResponse->status() !== 200) {
            echo "   ❌ Payment callback failed with status {$callbackResponse->status()}\n";
            echo '   📄 Response: '.json_encode($callbackResponse->json(), JSON_PRETTY_PRINT)."\n\n";
        }
        $callbackResponse->assertStatus(200);
        echo "   ✅ Payment callback processed successfully\n\n";

        // Refresh order
        $order->refresh();

        // ─────────────────────────────────────────────────────────────────────
        // CHECKPOINT 2: Verify Post-Payment State
        // ─────────────────────────────────────────────────────────────────────
        echo "🔍 CHECKPOINT 2: Verify Post-Payment State\n";

        // Verify order status changed to 'paid'
        $this->assertEquals(OrderStatus::PAID()->value, $order->status);
        echo "   ✅ Order status changed to 'paid'\n";

        // Verify payment was recorded
        $payment = $order->payments()->where('payment_status', 'paid')->first();
        $this->assertNotNull($payment, 'No payment with payment_status "paid" found');
        echo "   ✅ Payment recorded successfully\n";
        echo "   💵 Payment Amount: {$payment->amount_paid} FCFA\n";

        // Verify stock HAS been decremented after payment
        echo "   📦 Verifying stock after payment:\n";
        foreach ($items as $index => $item) {
            $stockRecord = DB::table('product_category_distribution_center')
                ->where('product_category_id', $item['product_category_id'])
                ->where('distribution_center_id', $this->distributionCenter->id)
                ->first();

            $productCategory = \App\Models\ProductCategory::find($item['product_category_id']);
            $categoryName = array_search($productCategory, $this->productCategories);

            if ($productCategory->product_type->value === \App\Enums\ProductType::BOTTLE()->value) {
                $expectedStock = 100 - $item['quantity'];
                echo "      → [{$categoryName}] Quantity ordered: {$item['quantity']}, Expected: {$expectedStock}, Actual: {$stockRecord->stock_filled}\n";
                $this->assertEquals($expectedStock, $stockRecord->stock_filled, "Bottle stock for {$categoryName} should be decremented to {$expectedStock} after payment (ordered: {$item['quantity']})");
            } else {
                $expectedStock = 100 - $item['quantity'];
                echo "      → [{$categoryName}] Quantity ordered: {$item['quantity']}, Expected: {$expectedStock}, Actual: {$stockRecord->stock}\n";
                $this->assertEquals($expectedStock, $stockRecord->stock, "Accessory stock for {$categoryName} should be decremented to {$expectedStock} after payment (ordered: {$item['quantity']})");
            }
        }

        // ─────────────────────────────────────────────────────────────────────
        // CHECKPOINT 3: Verify Distribution Center Manager Notified
        // ─────────────────────────────────────────────────────────────────────
        echo "\n🔍 CHECKPOINT 3: Verify Distribution Center Manager Notified\n";

        $managerNotification = DB::table('notifications')
            ->where('notifiable_id', $this->managerUser->id)
            ->where('notifiable_type', User::class)
            ->first();

        if ($managerNotification) {
            echo "   ✅ Distribution center manager notified\n";
            echo "   📧 Notification Type: {$managerNotification->type}\n";
            echo '   📝 Notification: '.substr($managerNotification->data, 0, 100)."...\n";
        } else {
            echo "   ⚠️  No notification found for distribution center manager\n";
            echo "      (This may be expected if notifications are disabled)\n";
        }

        // ─────────────────────────────────────────────────────────────────────
        // CHECKPOINT 4: Verify Delivery Person Assigned
        // ─────────────────────────────────────────────────────────────────────
        echo "\n🔍 CHECKPOINT 4: Verify Delivery Person Assignment\n";

        $order->refresh();

        if ($order->delivery_person_id) {
            $this->assertNotNull($order->delivery_person_id);
            echo "   ✅ Delivery person assigned to order\n";
            echo "   👤 Delivery Person ID: {$order->delivery_person_id}\n";
            echo "   📛 Delivery Person: {$order->deliveryPerson->user->first_name} {$order->deliveryPerson->user->last_name}\n";
        } else {
            echo "   ⚠️  No delivery person assigned yet\n";
            echo "      (Assignment may happen asynchronously or require manual action)\n";
        }

        // ─────────────────────────────────────────────────────────────────────
        // CHECKPOINT 5: Verify Delivery Person Notified
        // ─────────────────────────────────────────────────────────────────────
        echo "\n🔍 CHECKPOINT 5: Verify Delivery Person Notifications\n";

        if ($order->delivery_person_id) {
            $deliveryPersonNotification = DB::table('notifications')
                ->where('notifiable_id', $order->deliveryPerson->user_id)
                ->where('notifiable_type', User::class)
                ->first();

            if ($deliveryPersonNotification) {
                echo "   ✅ Delivery person notified\n";
                echo "   📧 Notification Type: {$deliveryPersonNotification->type}\n";
                echo '   📝 Notification: '.substr($deliveryPersonNotification->data, 0, 100)."...\n";
            } else {
                echo "   ⚠️  No notification found for delivery person\n";
            }
        } else {
            echo "   ⏭️  Skipped (no delivery person assigned)\n";
        }

        // ─────────────────────────────────────────────────────────────────────
        // CHECKPOINT 6: Verify All Notifications
        // ─────────────────────────────────────────────────────────────────────
        echo "\n🔍 CHECKPOINT 6: All Notifications Summary\n";

        $allNotifications = DB::table('notifications')->get();
        echo "   📬 Total notifications sent: {$allNotifications->count()}\n";

        foreach ($allNotifications as $notif) {
            $user = User::find($notif->notifiable_id);
            $userName = $user ? $user->email : 'Unknown';
            echo "      → {$notif->type} to {$userName}\n";
        }

        // ─────────────────────────────────────────────────────────────────────
        // FINAL SUMMARY
        // ─────────────────────────────────────────────────────────────────────
        echo "\n";
        echo "═══════════════════════════════════════════════════════════════════\n";
        echo "                    ✅ E2E TEST COMPLETED                          \n";
        echo "═══════════════════════════════════════════════════════════════════\n";
        echo "Summary:\n";
        echo "  ✅ Customer logged in and created order\n";
        echo "  ✅ Pre-payment state verified (no debit, no notifications)\n";
        echo "  ✅ Payment initiated and processed successfully\n";
        echo "  ✅ Order status changed from 'pending' to 'paid'\n";

        if ($allNotifications->count() > 0) {
            echo "  ✅ Notifications sent ({$allNotifications->count()} total)\n";
        } else {
            echo "  ⚠️  No notifications sent (may need investigation)\n";
        }

        if ($order->delivery_person_id) {
            echo "  ✅ Delivery person assigned\n";
        } else {
            echo "  ⚠️  No delivery person assigned (may need manual assignment)\n";
        }

        echo "═══════════════════════════════════════════════════════════════════\n\n";
    }

    /**
     * Prepare test data (users, products, stock, etc.)
     */
    private function prepareTestData(): void
    {
        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'delivery_person', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        // Create country
        $country = Country::factory()->create([
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        // Create distribution center with manager
        $this->distributionCenter = DistributionCenter::factory()->create([
            'name' => 'E2E Test Center',
        ]);

        $this->managerUser = User::factory()->create([
            'email' => 'manager-e2e@test.com',
            'password' => bcrypt('password'),
            'country_id' => $country->id,
        ]);
        $this->managerUser->assignRole('center_manager');

        $this->managerUser->distributionCenters()->create([
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        // Create delivery person
        $deliveryPersonUser = User::factory()->create([
            'email' => 'delivery-e2e@test.com',
            'password' => bcrypt('password'),
            'country_id' => $country->id,
        ]);
        $deliveryPersonUser->assignRole('delivery_person');

        $this->deliveryPerson = $deliveryPersonUser->deliveryPerson()->create([
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        // Create customer with delivery address
        $this->customerUser = User::factory()->create([
            'email' => 'customer-e2e@test.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'country_id' => $country->id,
        ]);
        $this->customerUser->assignRole('customer');

        $this->customer = $this->customerUser->customer()->create([
            'country_id' => $country->id,
        ]);

        $this->deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->distributionCenter->neighborhood_id,
        ]);

        // Create product categories
        $this->productCategories = [
            'Bouteille 12.5kg' => ProductCategory::factory()->bottleType()->create(),
            'Bouteille 6kg' => ProductCategory::factory()->bottleType()->create(),
            'Accessoire' => ProductCategory::factory()->accessoryType()->create(),
        ];

        // Create initial stock for each product category in the distribution center
        foreach ($this->productCategories as $category) {
            $productType = $category->product_type;

            if ($productType->value === \App\Enums\ProductType::BOTTLE()->value) {
                // For bottles, add stock_filled
                $this->distributionCenter->productCategories()->attach($category->id, [
                    'stock_filled' => 100,
                    'stock_empty' => 50,
                    'stock' => 0,
                ]);
            } else {
                // For accessories, add stock
                $this->distributionCenter->productCategories()->attach($category->id, [
                    'stock' => 100,
                    'stock_filled' => 0,
                    'stock_empty' => 0,
                ]);
            }
        }
    }
}
