<?php

namespace Tests\Feature\Notifications;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Events\OrderStatusChanged;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\DeliveryPerson;
use App\Models\User;
use App\Models\UserDistributionCenter;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
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

    public function test_order_status_changed_event_is_dispatched_when_order_status_changes()
    {
        // Create necessary related models first
        $customerUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $distributionCenter = DistributionCenter::factory()->create();

        // Create the order with initial status
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'status' => OrderStatus::PENDING(),
        ]);

        // Now fake events after creation
        Event::fake([OrderStatusChanged::class]);

        // Use OrderService to update the order status - this should trigger the event
        $orderService = app(\App\Services\Order\OrderService::class);
        $orderService->update($order, ['status' => OrderStatus::DELIVERED()->value]);

        Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order) {
            return $event->order->id === $order->id &&
                   $event->oldStatus &&
                   $event->oldStatus->value === OrderStatus::PENDING()->value &&
                   $event->newStatus->value === OrderStatus::DELIVERED()->value;
        });
    }

    public function test_order_status_changed_notifications_are_sent_to_customer_and_manager()
    {
        Notification::fake();

        $customerUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);

        $managerUser = User::factory()->create();
        // Assign the center manager role to the user
        $managerUser->assignRole(UserRole::CENTER_MANAGER()->value);

        $distributionCenter = DistributionCenter::factory()->create();

        // Create the relationship between user and distribution center
        UserDistributionCenter::create([
            'user_id' => $managerUser->id,
            'distribution_center_id' => $distributionCenter->id,
        ]);

        // Create order without events using mass assignment trick
        $orderData = [
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $customer->deliveryAddresses()->first()?->id ?? CustomerDeliveryAddress::factory()->create(['customer_id' => $customer->id])->id,
            'order_number' => 'TEST-123456',
            'status' => OrderStatus::PENDING(),
            'subtotal' => 1000,
            'delivery_fee' => 500,
            'total_amount' => 1500,
            'order_date' => now(),
            'delivery_type' => 'normal',
        ];

        $order = new Order;
        $order->forceFill($orderData);
        $order->saveQuietly(); // Save without firing events

        // Now manually trigger the listener
        $listener = new \App\Listeners\Order\SendOrderStatusChangedNotification;
        $listener->handle(new OrderStatusChanged($order, OrderStatus::PENDING(), OrderStatus::DELIVERED()));

        Notification::assertSentTo($customerUser, OrderStatusChangedNotification::class);
        Notification::assertSentTo($managerUser, OrderStatusChangedNotification::class);
    }
}
