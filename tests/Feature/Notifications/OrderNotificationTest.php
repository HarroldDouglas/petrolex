<?php

namespace Tests\Feature\Notifications;

use App\Enums\UserRole;
use App\Events\OrderStatusChanged;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Order;
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

    public function test_order_status_changed_event_is_dispatched_when_order_is_created()
    {
        Event::fake([OrderStatusChanged::class]);

        // Create necessary related models first
        $customerUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $distributionCenter = DistributionCenter::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
        ]);

        Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order) {
            return $event->order->id === $order->id &&
                   $event->oldStatus === null &&
                   $event->newStatus === 'confirmed'; // Default status from factory
        });
    }

    public function test_order_status_changed_event_is_dispatched_when_order_status_changes()
    {
        Event::fake([OrderStatusChanged::class]);

        // Create necessary related models first
        $customerUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        $distributionCenter = DistributionCenter::factory()->create();

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'status' => 'pending',
        ]);

        $order->update(['status' => 'delivered']);

        Event::assertDispatched(OrderStatusChanged::class, function ($event) use ($order) {
            return $event->order->id === $order->id &&
                   $event->oldStatus === 'pending' &&
                   $event->newStatus === 'delivered';
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
            'status' => 'pending',
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
        $listener = new \App\Listeners\SendOrderStatusChangedNotification;
        $listener->handle(new OrderStatusChanged($order, 'pending', 'delivered'));

        Notification::assertSentTo($customerUser, OrderStatusChangedNotification::class);
        Notification::assertSentTo($managerUser, OrderStatusChangedNotification::class);
    }
}
