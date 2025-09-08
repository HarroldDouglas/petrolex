<?php

namespace Tests\Feature\Notifications;

use App\Events\OrderCreatedEvent;
use App\Events\OrderDeliveredEvent;
use App\Events\OrderCancelledEvent;
use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\UserDistributionCenter;
use App\Enums\UserRole;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderDeliveredNotification;
use App\Notifications\OrderCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_created_event_is_dispatched_when_order_is_created()
    {
        Event::fake();

        $order = Order::factory()->create();

        Event::assertDispatched(OrderCreatedEvent::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    /** @test */
    public function order_delivered_event_is_dispatched_when_order_status_changes_to_delivered()
    {
        Event::fake();

        $order = Order::factory()->create(['status' => 'pending']);
        
        $order->update(['status' => 'delivered']);

        Event::assertDispatched(OrderDeliveredEvent::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    /** @test */
    public function order_cancelled_event_is_dispatched_when_order_status_changes_to_cancelled()
    {
        Event::fake();

        $order = Order::factory()->create(['status' => 'pending']);
        
        $order->update(['status' => 'cancelled']);

        Event::assertDispatched(OrderCancelledEvent::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    /** @test */
    public function order_created_notifications_are_sent_to_customer_and_manager()
    {
        Notification::fake();

        $customerUser = User::factory()->create();
        $customer = Customer::factory()->create(['user_id' => $customerUser->id]);
        
        $managerUser = User::factory()->create();
        // Assign the center manager role to the user
        $managerUser->assignRole(UserRole::CENTER_MANAGER());
        
        $distributionCenter = DistributionCenter::factory()->create();
        
        // Create the relationship between user and distribution center
        UserDistributionCenter::factory()->create([
            'user_id' => $managerUser->id,
            'distribution_center_id' => $distributionCenter->id,
        ]);
        
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
        ]);

        // Manually dispatch the event since we're testing the listener behavior
        event(new OrderCreatedEvent($order));

        Notification::assertSentTo($customerUser, OrderCreatedNotification::class);
        Notification::assertSentTo($managerUser, OrderCreatedNotification::class);
    }
}
