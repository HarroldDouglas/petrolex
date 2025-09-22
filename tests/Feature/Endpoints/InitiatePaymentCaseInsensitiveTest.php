<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InitiatePaymentCaseInsensitiveTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Customer $customer;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_accepts_payment_method_in_uppercase_orange_money()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$this->order->id}/payment", [
                'payment_method' => 'ORANGE_MONEY',
                'payment_details' => [
                    'phone' => '677123456',
                    'name' => 'Jean Dupont',
                ],
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '_metadata' => [
                'success',
                'message',
            ],
            'data',
        ]);
    }

    #[Test]
    public function it_accepts_payment_method_in_mixed_case_mtn_money()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$this->order->id}/payment", [
                'payment_method' => 'Mtn_Money',
                'payment_details' => [
                    'phone' => '677123456',
                    'name' => 'Jean Dupont',
                ],
            ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_accepts_payment_method_in_lowercase_credit_card()
    {
        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$this->order->id}/payment", [
                'payment_method' => 'credit_card',
                'payment_details' => [
                    'card_number' => '4111111111111111',
                    'cvv' => '123',
                    'expiry_date' => '12/25',
                    'cardholder_name' => 'Jean Dupont',
                ],
            ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function it_accepts_all_valid_payment_methods_in_different_cases()
    {
        $validPaymentMethods = [
            'orange_money' => [
                'phone' => '677123456',
                'name' => 'Jean Dupont',
            ],
            'ORANGE_MONEY' => [
                'phone' => '677123456',
                'name' => 'Jean Dupont',
            ],
            'Orange_Money' => [
                'phone' => '677123456',
                'name' => 'Jean Dupont',
            ],
            'mtn_money' => [
                'phone' => '677123456',
                'name' => 'Jean Dupont',
            ],
            'MTN_MONEY' => [
                'phone' => '677123456',
                'name' => 'Jean Dupont',
            ],
            'credit_card' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_date' => '12/25',
                'cardholder_name' => 'Jean Dupont',
            ],
            'CREDIT_CARD' => [
                'card_number' => '4111111111111111',
                'cvv' => '123',
                'expiry_date' => '12/25',
                'cardholder_name' => 'Jean Dupont',
            ],
        ];

        foreach ($validPaymentMethods as $paymentMethod => $details) {
            // Create a new order for each test to avoid conflicts
            $order = Order::factory()->create([
                'customer_id' => $this->customer->id,
                'status' => 'pending',
            ]);

            $response = $this->actingAs($this->user)
                ->postJson("/api/orders/{$order->id}/payment", [
                    'payment_method' => $paymentMethod,
                    'payment_details' => $details,
                ]);

            $response->assertStatus(200, "Failed for payment_method: {$paymentMethod}");
        }
    }

    #[Test]
    public function it_rejects_invalid_payment_methods()
    {
        $invalidPaymentMethods = ['paypal', 'PAYPAL', 'bank_transfer', 'cash'];

        foreach ($invalidPaymentMethods as $paymentMethod) {
            $response = $this->actingAs($this->user)
                ->postJson("/api/orders/{$this->order->id}/payment", [
                    'payment_method' => $paymentMethod,
                    'payment_details' => [
                        'phone' => '677123456',
                        'name' => 'Jean Dupont',
                    ],
                ]);

            $response->assertStatus(422, "Should reject invalid payment_method: {$paymentMethod}");
            $response->assertJsonValidationErrors('payment_method');
        }
    }

    #[Test]
    public function it_validates_required_fields_based_on_normalized_payment_method()
    {
        // Test that when we send 'ORANGE_MONEY', it still validates the correct fields
        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$this->order->id}/payment", [
                'payment_method' => 'ORANGE_MONEY',
                'payment_details' => [
                    // Missing phone and name
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_details.phone', 'payment_details.name']);
    }
}
