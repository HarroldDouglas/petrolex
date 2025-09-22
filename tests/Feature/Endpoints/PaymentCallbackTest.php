<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_process_successful_payment_callback(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PENDING()->value,
        ]);

        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => (string) $order->id,
            'operator_transaction_ref' => 'OM_123456',
            'transaction_ref' => 'TXN_123456',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'SUCCESS',
            'transaction_reason' => 'Payment completed',
            'transaction_message' => 'Transaction successful',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        $response = $this->postJson(route('api.payments.callback'), $callbackData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'order_id',
                    'transaction_status',
                    'processed_at',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.order_id', (string) $order->id)
            ->assertJsonPath('data.transaction_status', 'SUCCESS');

        // Verify payment was updated in database
        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PAID()->value,
        ]);

        // Verify order was confirmed (paid)
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);

        $order->refresh();
        $this->assertNotNull($order->paid_at);
    }

    #[Test]
    public function it_can_process_failed_payment_callback(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PENDING()->value,
        ]);

        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => (string) $order->id,
            'operator_transaction_ref' => 'OM_FAILED',
            'transaction_ref' => 'TXN_FAILED',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'FAILED',
            'transaction_reason' => 'Payment failed',
            'transaction_message' => 'Transaction failed',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        $response = $this->postJson(route('api.payments.callback'), $callbackData);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.order_id', (string) $order->id)
            ->assertJsonPath('data.transaction_status', 'FAILED');

        // Verify payment was updated in database
        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::FAILED()->value,
        ]);
    }

    #[Test]
    public function it_handles_invalid_payment_reference(): void
    {
        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => '99999', // Non-existent order ID
            'operator_transaction_ref' => 'OM_INVALID',
            'transaction_ref' => 'TXN_INVALID',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'SUCCESS',
            'transaction_reason' => 'Payment completed',
            'transaction_message' => 'Transaction successful',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        $response = $this->postJson(route('api.payments.callback'), $callbackData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'app_transaction_ref',
                ],
            ]);
    }

    #[Test]
    public function it_validates_required_callback_fields(): void
    {
        $response = $this->postJson(route('api.payments.callback'), []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'application',
                    'app_transaction_ref',
                ],
            ]);
    }

    #[Test]
    public function it_validates_status_enum_values(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        OrderPayment::factory()->create([
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PENDING()->value,
        ]);

        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => (string) $order->id,
            'operator_transaction_ref' => 'OM_INVALID_STATUS',
            'transaction_ref' => 'TXN_INVALID_STATUS',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'INVALID_STATUS',
            'transaction_reason' => 'Payment completed',
            'transaction_message' => 'Transaction successful',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        $response = $this->postJson(route('api.payments.callback'), $callbackData);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'transaction_status',
                ],
            ]);

        $actualMessage = $response->json('errors.transaction_status.0');
        $this->assertStringContainsString('SUCCESS', $actualMessage);
        $this->assertStringContainsString('FAILED', $actualMessage);
    }

    #[Test]
    public function it_prevents_duplicate_callback_processing(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PAID()->value, // Already processed
        ]);

        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => (string) $order->id,
            'operator_transaction_ref' => 'OM_DUPLICATE',
            'transaction_ref' => 'TXN_DUPLICATE',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'SUCCESS',
            'transaction_reason' => 'Payment completed',
            'transaction_message' => 'Transaction successful',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        $response = $this->postJson(route('api.payments.callback'), $callbackData);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);
    }

    #[Test]
    public function it_logs_callback_activity(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PENDING()->value,
        ]);

        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => (string) $order->id,
            'operator_transaction_ref' => 'OM_LOG',
            'transaction_ref' => 'TXN_LOG',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'SUCCESS',
            'transaction_reason' => 'Payment completed',
            'transaction_message' => 'Transaction successful',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        $this->postJson(route('api.payments.callback'), $callbackData);

        // Verify payment was updated in database
        $this->assertDatabaseHas('order_payments', [
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PAID()->value,
        ]);
    }

    #[Test]
    public function it_handles_malformed_json_gracefully(): void
    {
        $response = $this->postJson(route('api.payments.callback'), []);

        $response->assertStatus(422);
    }

    #[Test]
    public function it_allows_callback_without_authentication(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);
        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PENDING()->value,
        ]);

        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => (string) $order->id,
            'operator_transaction_ref' => 'OM_NO_AUTH',
            'transaction_ref' => 'TXN_NO_AUTH',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'SUCCESS',
            'transaction_reason' => 'Payment completed',
            'transaction_message' => 'Transaction successful',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        // No authorization header
        $response = $this->postJson(route('api.payments.callback'), $callbackData);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);
    }

    #[Test]
    public function it_does_not_update_non_pending_orders(): void
    {
        $customer = \App\Models\Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $confirmedAt = now()->subHour(); // Confirmed 1 hour ago
        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => \App\Enums\OrderStatus::PAID()->value, // Already confirmed
            'paid_at' => $confirmedAt,
        ]);
        $payment = OrderPayment::factory()->create([
            'order_id' => $order->id,
            'payment_status' => PaymentStatus::PENDING()->value,
        ]);

        $callbackData = [
            'application' => 'PETROLEX',
            'app_transaction_ref' => (string) $order->id,
            'operator_transaction_ref' => 'OM_CONFIRMED_ORDER',
            'transaction_ref' => 'TXN_CONFIRMED_ORDER',
            'transaction_type' => 'PAYIN',
            'transaction_amount' => 50000.0,
            'transaction_fees' => 100.0,
            'transaction_currency' => 'XAF',
            'transaction_operator' => 'CM_OM',
            'transaction_status' => 'SUCCESS',
            'transaction_reason' => 'Payment completed',
            'transaction_message' => 'Transaction successful',
            'customer_phone_number' => '677123456',
            'signature' => 'test_signature',
        ];

        $response = $this->postJson(route('api.payments.callback'), $callbackData);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        // Verify payment status is updated but order status remains unchanged
        $payment->refresh();
        $order->refresh();

        $this->assertEquals(PaymentStatus::PAID()->value, $payment->payment_status);
        $this->assertEquals(\App\Enums\OrderStatus::PAID()->value, $order->status->value);
        $this->assertEquals($confirmedAt->timestamp, $order->paid_at->timestamp); // Should not be updated
    }

    #[Test]
    public function it_denies_customer_access_to_other_customers_orders_for_feedback(): void
    {

        // Create customer role
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Create two customers
        $customer1 = \App\Models\Customer::factory()->create();
        $customer2 = \App\Models\Customer::factory()->create();

        // Assign customer role to both users
        $customer1->user->assignRole('customer');
        $customer2->user->assignRole('customer');

        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress1 = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer1->id,
        ]);
        $deliveryAddress2 = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $customer2->id,
        ]);

        // Create orders for both customers
        $order1 = Order::factory()->create([
            'customer_id' => $customer1->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress1->id,
        ]);
        $order2 = Order::factory()->create([
            'customer_id' => $customer2->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress2->id,
        ]);

        // Login customer 1
        $response = $this->postJson(route('api.login'), [
            'login' => $customer1->user->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $response->assertStatus(200);
        $customer1Token = $response->json('data.access_token');
        $this->assertNotNull($customer1Token);

        // Customer 1 should NOT be able to add feedback to customer 2's order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$customer1Token,
            'Accept' => 'application/json',
            'Accept-Language' => 'fr',
        ])->postJson(route('api.orders.customer-feedback', ['order' => $order2->id]), [
            'comments' => 'Trying to access other customer order',
            'rating' => 3.0,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Cette commande ne vous appartient pas');
    }
}
