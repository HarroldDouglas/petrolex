<?php

namespace Tests\Feature\Mail;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Mail\Order\OrderCreatedMail;
use App\Mail\Order\OrderStatusChangedMail;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderEmailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private DistributionCenter $distributionCenter;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create all necessary roles
        foreach (UserRole::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        $this->user = User::factory()->create(['email' => 'test@example.com']);
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
        $this->distributionCenter = DistributionCenter::factory()->create();

        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::CONFIRMED(),
        ]);
    }

    public function test_order_created_email_template()
    {
        $mail = new OrderCreatedMail($this->order, $this->user);

        $envelope = $mail->envelope();
        $content = $mail->content();

        $this->assertCount(1, $envelope->to);
        $this->assertEquals($this->user->email, $envelope->to[0]->address);
        $this->assertStringContainsString($this->order->order_number, $envelope->subject);
        $this->assertEquals('emails.orders.order-created', $content->view);
    }

    public function test_order_confirmed_email_template()
    {
        $mail = new OrderStatusChangedMail($this->order, $this->user, null, OrderStatus::CONFIRMED());

        $content = $mail->content();

        $this->assertEquals('emails.orders.order-confirmed', $content->view);
    }

    public function test_order_processing_email_template()
    {
        $this->order->update(['status' => OrderStatus::PROCESSING()]);
        $mail = new OrderStatusChangedMail($this->order, $this->user, OrderStatus::CONFIRMED(), OrderStatus::PROCESSING());

        $content = $mail->content();

        $this->assertEquals('emails.orders.order-processing', $content->view);
    }

    public function test_order_delivered_email_template()
    {
        $this->order->update(['status' => OrderStatus::DELIVERED()]);
        $mail = new OrderStatusChangedMail($this->order, $this->user, OrderStatus::PROCESSING(), OrderStatus::DELIVERED());

        $content = $mail->content();

        $this->assertEquals('emails.orders.order-delivered', $content->view);
    }

    public function test_order_cancelled_email_template()
    {
        $this->order->update(['status' => OrderStatus::CANCELLED()]);
        $mail = new OrderStatusChangedMail($this->order, $this->user, OrderStatus::CONFIRMED(), OrderStatus::CANCELLED());

        $content = $mail->content();

        $this->assertEquals('emails.orders.order-cancelled', $content->view);
    }

    public function test_order_pending_email_template()
    {
        $this->order->update(['status' => OrderStatus::PENDING()]);
        $mail = new OrderStatusChangedMail($this->order, $this->user, null, OrderStatus::PENDING());

        $content = $mail->content();

        $this->assertEquals('emails.orders.order-pending', $content->view);
    }

    public function test_order_paid_email_template()
    {
        $this->order->update(['status' => OrderStatus::PAID()]);
        $mail = new OrderStatusChangedMail($this->order, $this->user, OrderStatus::PENDING(), OrderStatus::PAID());

        $content = $mail->content();

        $this->assertEquals('emails.orders.order-paid', $content->view);
    }

    public function test_order_payment_failed_email_template()
    {
        $this->order->update(['status' => OrderStatus::FAILED()]);
        $mail = new OrderStatusChangedMail($this->order, $this->user, OrderStatus::PENDING(), OrderStatus::FAILED());

        $content = $mail->content();

        $this->assertEquals('emails.orders.order-payment-failed', $content->view);
    }

    public function test_fallback_to_default_template_for_unknown_status()
    {
        // Test that unknown status falls back to default template
        $mail = new OrderStatusChangedMail($this->order, $this->user);

        $content = $mail->content();

        // Should use the default template for confirmed status
        $this->assertEquals('emails.orders.order-confirmed', $content->view);
    }
}
