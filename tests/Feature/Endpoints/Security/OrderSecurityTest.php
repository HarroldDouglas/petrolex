<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints\Security;

use App\Models\Customer;
use App\Models\Geography\Country;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;
    private Customer $customer;
    private string $customerToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'delivery_person', 'guard_name' => 'web']);

        // Create country
        $country = Country::factory()->create([
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        // Create customer user
        $this->customerUser = User::factory()->create([
            'country_id' => $country->id,
        ]);
        $this->customerUser->assignRole('customer');

        // Create customer
        $this->customer = Customer::factory()->create([
            'user_id' => $this->customerUser->id,
        ]);

        // Login and get token
        $response = $this->postJson(route('api.login'), [
            'login' => $this->customerUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $this->customerToken = $response->json('data.access_token');
    }

    #[Test]
    public function customer_cannot_download_invoice_for_other_customers_order(): void
    {
        // Create another customer and their order
        $otherCustomer = Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Try to download invoice for other customer's order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->customerToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.download.invoice', ['order' => $otherOrder->id]));

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Cette facture ne vous appartient pas');
    }

    #[Test]
    public function admin_can_download_invoice_for_any_order(): void
    {
        // Create admin user
        $adminUser = User::factory()->create();
        $adminUser->assignRole('admin');

        // Login admin
        $response = $this->postJson(route('api.login'), [
            'login' => $adminUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $adminToken = $response->json('data.access_token');

        // Create order for our customer
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Admin should be able to download invoice
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$adminToken,
            'Accept' => 'application/pdf',
        ])->getJson(route('api.orders.download.invoice', ['order' => $order->id]));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function delivery_person_can_download_invoice_for_any_order(): void
    {
        // Create delivery person user
        $deliveryUser = User::factory()->create();
        $deliveryUser->assignRole('delivery_person');

        // Login delivery person
        $response = $this->postJson(route('api.login'), [
            'login' => $deliveryUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $deliveryToken = $response->json('data.access_token');

        // Create order for our customer
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Delivery person should be able to download invoice
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$deliveryToken,
            'Accept' => 'application/pdf',
        ])->getJson(route('api.orders.download.invoice', ['order' => $order->id]));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }

    #[Test]
    public function customer_cannot_mark_other_customers_order_as_delivered(): void
    {
        // Create another customer and their order
        $otherCustomer = Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Try to mark other customer's order as delivered
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->customerToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.orders.deliver', ['order' => $otherOrder->id]));

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are not authorized to mark this order as delivered');
    }

    #[Test]
    public function customer_cannot_scan_bottles_for_other_customers_order(): void
    {
        // Create another customer and their order
        $otherCustomer = Customer::factory()->create();
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Create order item for the other order
        $productCategory = \App\Models\ProductCategory::factory()->create();
        $orderItem = \App\Models\OrderItem::factory()->create([
            'order_id' => $otherOrder->id,
            'product_category_id' => $productCategory->id,
        ]);

        // Try to scan bottle for other customer's order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->customerToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.scan-empty-bottle', ['order' => $otherOrder->id]), [
            'barcode' => 'TEST_BARCODE_123',
            'order_item_id' => $orderItem->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You are not authorized to scan bottles for this order');
    }

    #[Test]
    public function delivery_person_can_scan_bottles_for_any_order(): void
    {
        // Create delivery person user
        $deliveryUser = User::factory()->create();
        $deliveryUser->assignRole('delivery_person');

        // Login delivery person
        $response = $this->postJson(route('api.login'), [
            'login' => $deliveryUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $deliveryToken = $response->json('data.access_token');

        // Create order for our customer
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Create order item
        $productCategory = \App\Models\ProductCategory::factory()->create();
        $orderItem = \App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_category_id' => $productCategory->id,
        ]);

        // Delivery person should be able to scan bottles
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$deliveryToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.scan-empty-bottle', ['order' => $order->id]), [
            'barcode' => 'TEST_BARCODE_123',
            'order_item_id' => $orderItem->id,
        ]);

        // The scan might fail due to business logic, but security should allow access
        // Should NOT be 403 (forbidden) - any other status is acceptable for this security test
        $this->assertNotEquals(403, $response->status(), 'Should not be forbidden - delivery person should have access');
    }
}
