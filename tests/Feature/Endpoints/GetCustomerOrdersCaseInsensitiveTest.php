<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetCustomerOrdersCaseInsensitiveTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_accepts_status_filter_in_uppercase()
    {
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my/orders?status=PENDING');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '_metadata' => ['success', 'message'],
            'data' => [],
        ]);
    }

    #[Test]
    public function it_accepts_status_filter_in_mixed_case()
    {
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'processing',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my/orders?status=PROCESSING');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_accepts_delivery_type_filter_in_uppercase()
    {
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'delivery_type' => 'normal',
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my/orders?delivery_type=NORMAL');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_accepts_payment_method_filter_in_mixed_case()
    {
        Order::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/my/orders?payment_method=Orange_Money');

        $response->assertStatus(200);
    }

    #[Test]
    public function it_rejects_invalid_status_values()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/my/orders?status=confirmed');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    #[Test]
    public function it_accepts_all_valid_status_values_in_different_cases()
    {
        $validStatuses = [
            'pending', 'PENDING', 'Pending',
            'processing', 'PROCESSING', 'Processing',
            'delivered', 'DELIVERED', 'Delivered',
            'cancelled', 'CANCELLED', 'Cancelled',
            'paid', 'PAID', 'Paid',
            'failed', 'FAILED', 'Failed',
        ];

        foreach ($validStatuses as $status) {
            $response = $this->actingAs($this->user)
                ->getJson("/api/my/orders?status={$status}");

            $response->assertStatus(200, "Failed for status: {$status}");
        }
    }

    #[Test]
    public function it_rejects_confirmed_status_which_does_not_exist_in_enum()
    {
        $invalidStatuses = ['confirmed', 'CONFIRMED', 'Confirmed'];

        foreach ($invalidStatuses as $status) {
            $response = $this->actingAs($this->user)
                ->getJson("/api/my/orders?status={$status}");

            $response->assertStatus(422, "Should reject invalid status: {$status}");
            $response->assertJsonValidationErrors('status');
        }
    }

    #[Test]
    public function it_accepts_delivery_types_in_different_cases()
    {
        $validDeliveryTypes = [
            'normal', 'NORMAL', 'Normal',
            'fast', 'FAST', 'Fast',
        ];

        foreach ($validDeliveryTypes as $deliveryType) {
            $response = $this->actingAs($this->user)
                ->getJson("/api/my/orders?delivery_type={$deliveryType}");

            $response->assertStatus(200, "Failed for delivery_type: {$deliveryType}");
        }
    }

    #[Test]
    public function it_accepts_payment_methods_in_different_cases()
    {
        $validPaymentMethods = [
            'orange_money', 'ORANGE_MONEY', 'Orange_Money',
            'mtn_money', 'MTN_MONEY', 'Mtn_Money',
            'credit_card', 'CREDIT_CARD', 'Credit_Card',
        ];

        foreach ($validPaymentMethods as $paymentMethod) {
            $response = $this->actingAs($this->user)
                ->getJson("/api/my/orders?payment_method={$paymentMethod}");

            $response->assertStatus(200, "Failed for payment_method: {$paymentMethod}");
        }
    }
}
