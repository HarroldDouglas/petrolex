<?php

namespace Tests\Feature\Notifications;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use App\Models\UserDistributionCenter;
use App\Notifications\OrderStatusChangedNotification;
use App\Services\Order\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderDeliveryNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create all necessary roles
        foreach (UserRole::cases() as $role) {
            Role::create(['name' => $role->value]);
        }
    }

    public function test_order_delivery_sends_notification_only_once()
    {
        Notification::fake();
        // Don't fake events - let them run so listeners execute and send notifications

        // Create necessary related models
        $customerUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);

        $managerUser = User::factory()->create();
        $managerUser->assignRole(UserRole::CENTER_MANAGER()->value);

        $distributionCenter = DistributionCenter::factory()->create();

        UserDistributionCenter::create([
            'user_id' => $managerUser->id,
            'distribution_center_id' => $distributionCenter->id,
        ]);

        // Create order without triggering events to avoid counting creation events
        $order = Order::factory()->make([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'status' => OrderStatus::PROCESSING(),
        ]);
        $order->saveQuietly(); // Save without triggering observers

        // Use the order service to deliver the order
        $orderService = app(OrderService::class);
        $orderService->deliverOrder($order);

        // Assert that notifications were sent exactly once to each recipient
        Notification::assertSentToTimes($customerUser, OrderStatusChangedNotification::class, 1);
        Notification::assertSentToTimes($managerUser, OrderStatusChangedNotification::class, 1);

        // Verify the notification content
        Notification::assertSentTo($customerUser, OrderStatusChangedNotification::class, function ($notification) use ($order) {
            return $notification->order->id === $order->id;
        });

        Notification::assertSentTo($managerUser, OrderStatusChangedNotification::class, function ($notification) use ($order) {
            return $notification->order->id === $order->id;
        });
    }

    public function test_order_delivery_updates_status_correctly()
    {
        // Create necessary related models
        $customerUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $distributionCenter = DistributionCenter::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'status' => OrderStatus::PROCESSING(),
        ]);

        $orderService = app(OrderService::class);
        $deliveredOrder = $orderService->deliverOrder($order);

        $this->assertNotNull($deliveredOrder);
        $this->assertEquals(OrderStatus::DELIVERED()->value, $deliveredOrder->status->value);

        // Refresh the order from database to confirm the change was persisted
        $order->refresh();
        $this->assertEquals(OrderStatus::DELIVERED()->value, $order->status->value);
    }
}
