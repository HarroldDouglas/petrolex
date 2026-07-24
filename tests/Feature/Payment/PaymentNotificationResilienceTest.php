<?php

declare(strict_types=1);

namespace Tests\Feature\Payment;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Events\OrderStatusChanged;
use App\Listeners\Order\SendOrderStatusChangedNotification;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Regression guard for the production incident where a failing email
 * (SMTP "550 No Such User Here") sent synchronously inside the payment
 * confirmation transaction rolled the whole payment back — customer debited,
 * order never marked paid.
 *
 * The fix: order notifications are queued + dispatched after commit, and the
 * status-changed listener swallows notification failures. A broken mailbox
 * must never be able to undo a confirmed payment.
 */
final class PaymentNotificationResilienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value]);
        }

        SendOrderStatusChangedNotification::clearProcessedEvents();
    }

    /**
     * Structural guarantee: the notifications reachable from the payment
     * transaction are queued and only dispatched after the DB commit, so they
     * physically cannot run inside (and roll back) that transaction.
     *
     * @return array<int, class-string>
     */
    public static function paymentPathNotifications(): array
    {
        return [
            [OrderStatusChangedNotification::class],
            [OrderCreatedNotification::class],
        ];
    }

    #[Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('paymentPathNotifications')]
    public function payment_path_notifications_are_queued_and_dispatched_after_commit(string $notification): void
    {
        $this->assertTrue(
            is_subclass_of($notification, ShouldQueue::class),
            "{$notification} must implement ShouldQueue so it is sent outside the payment transaction."
        );

        // Instantiated with a bare (unsaved) Order — the constructor only stores
        // it and flips afterCommit; it never touches the database.
        $instance = (new ReflectionClass($notification))->newInstance(new Order);

        $this->assertTrue(
            $instance->afterCommit === true,
            "{$notification} must set \$afterCommit = true so it dispatches only after the transaction commits."
        );
    }

    #[Test]
    public function a_failing_notification_does_not_bubble_out_of_the_status_listener(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::PAID()->value,
            'paid_at' => now(),
        ]);

        // The customer has a real email, so the listener WILL attempt to notify.
        $this->assertNotNull($order->customer->user->email);

        // Simulate the broken mailbox: sending blows up exactly like the incident.
        Notification::shouldReceive('send')
            ->andThrow(new \RuntimeException('SMTP 550 No Such User Here'));

        $listener = new SendOrderStatusChangedNotification;
        $event = new OrderStatusChanged($order->fresh(), OrderStatus::PENDING(), OrderStatus::PAID());

        // Must NOT throw — the listener swallows notification failures. If the
        // try/catch were removed, this exception would propagate and (in the
        // real callback) roll back the payment.
        $listener->handle($event);

        // Reaching this line proves the failure was contained.
        $this->assertTrue(true);
    }
}
