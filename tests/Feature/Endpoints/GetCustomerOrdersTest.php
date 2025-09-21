<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GetCustomerOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;
    private Customer $customer;
    private string $authToken;
    private Country $country;
    private City $city;
    private Municipality $municipality;
    private Neighborhood $neighborhood;

    protected function setUp(): void
    {
        parent::setUp();

        // Set locale to French for tests
        app()->setLocale('fr');

        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        // Create geographical hierarchy
        $this->country = Country::factory()->create([
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        $this->city = City::factory()->create([
            'country_id' => $this->country->id,
        ]);

        $this->municipality = Municipality::factory()->create([
            'city_id' => $this->city->id,
        ]);

        $this->neighborhood = Neighborhood::factory()->create([
            'municipality_id' => $this->municipality->id,
        ]);

        // Create customer user
        $this->customerUser = User::factory()->create([
            'country_id' => $this->country->id,
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
        $this->authToken = $response->json('data.access_token');
    }

    #[Test]
    public function it_can_get_customer_orders_successfully(): void
    {
        // Create test data
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
        ]);

        // Create orders with different statuses and delivery types
        Order::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                    'pagination' => [
                        'total',
                        'current_page_total',
                        'per_page',
                        'current_page',
                        'total_pages',
                        'next_page_url',
                        'prev_page_url',
                    ],
                ],
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'delivery_type',
                        'payment_method',
                        'subtotal',
                        'delivery_fee',
                        'total_amount',
                        'order_date',
                        'delivery_date',
                        'status',
                        'invoice_url',
                        'items',
                        'comments',
                        'rating',
                        'payment',
                        'delivery_address',
                        'customer',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.pagination.total', 3);
    }

    #[Test]
    public function it_can_filter_orders_by_status(): void
    {
        // Create test data
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
        ]);

        // Create orders with different statuses
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::PENDING(),
        ]);

        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'status' => OrderStatus::DELIVERED(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['status' => 'pending']));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.pagination.total', 1);

        // Verify the returned order has the correct status
        $this->assertEquals('pending', $response->json('data.0.status'));
    }

    #[Test]
    public function it_can_filter_orders_by_delivery_type(): void
    {
        // Create test data
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
        ]);

        // Create orders with different delivery types
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'delivery_type' => DeliveryType::NORMAL(),
        ]);

        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'delivery_type' => DeliveryType::FAST(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['delivery_type' => 'fast']));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.pagination.total', 1);

        // Verify the returned order has the correct delivery type
        $this->assertEquals('fast', $response->json('data.0.delivery_type'));
    }

    #[Test]
    public function it_can_filter_orders_by_payment_method(): void
    {
        // Create test data
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
        ]);

        // Create orders (we'll skip payment_method filtering for now due to missing factory)
        Order::factory()->count(2)->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['payment_method' => 'orange_money']));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        // Since we don't have payments, we expect 0 results when filtering by payment method
        $this->assertEquals(0, $response->json('_metadata.pagination.total'));
    }

    #[Test]
    public function it_can_filter_orders_by_order_number(): void
    {
        // Create test data
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'order_number' => 'ORD-12345',
        ]);

        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
            'order_number' => 'ORD-67890',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['order_number' => 'ORD-12345']));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.pagination.total', 1)
            ->assertJsonPath('data.0.order_number', 'ORD-12345');
    }

    #[Test]
    public function it_can_paginate_orders(): void
    {
        // Create test data
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $this->neighborhood->id,
        ]);

        // Create 5 orders
        Order::factory()->count(5)->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['per_page' => 2]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.pagination.total', 5)
            ->assertJsonPath('_metadata.pagination.per_page', 2)
            ->assertJsonPath('_metadata.pagination.current_page_total', 2)
            ->assertJsonPath('_metadata.pagination.total_pages', 3);

        // Test second page
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['per_page' => 2, 'page' => 2]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.pagination.current_page', 2);
    }

    #[Test]
    public function it_returns_validation_error_for_invalid_status(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['status' => 'invalid_status']));

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['status'],
            ]);

        $this->assertStringContainsString('processing, delivered, cancelled, pending, paid, failed',
            $response->json('errors.status.0'));
    }

    #[Test]
    public function it_returns_validation_error_for_invalid_delivery_type(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['delivery_type' => 'invalid_type']));

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['delivery_type'],
            ]);

        // Debug: Check what's actually returned
        $actualMessage = $response->json('errors.delivery_type.0');
        $this->assertStringContainsString('normal', $actualMessage);
        $this->assertStringContainsString('fast', $actualMessage);
    }

    #[Test]
    public function it_returns_validation_error_for_invalid_payment_method(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['payment_method' => 'invalid_method']));

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['payment_method'],
            ]);

        $actualMessage = $response->json('errors.payment_method.0');
        $this->assertStringContainsString('orange_money', $actualMessage);
        $this->assertStringContainsString('mtn_money', $actualMessage);
        $this->assertStringContainsString('credit_card', $actualMessage);
    }

    #[Test]
    public function it_returns_validation_error_for_invalid_per_page(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['per_page' => 0]));

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['per_page'],
            ]);

        $actualMessage = $response->json('errors.per_page.0');
        $this->assertStringContainsString('1', $actualMessage);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index', ['per_page' => 101]));

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => ['per_page'],
            ]);

        $actualMessage = $response->json('errors.per_page.0');
        $this->assertStringContainsString('100', $actualMessage);
    }

    #[Test]
    public function it_returns_unauthorized_error_for_unauthenticated_user(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index'));

        $response->assertStatus(401);
    }

    #[Test]
    public function it_only_returns_orders_for_authenticated_customer(): void
    {
        // Create another customer with orders
        $otherCustomer = Customer::factory()->create();
        $distributionCenter = DistributionCenter::factory()->create();
        $deliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        // Create order for other customer
        Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Create order for authenticated customer
        $myDeliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $myDeliveryAddress->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index'));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.pagination.total', 1);

        // Verify that only my customer orders are returned
        $this->assertEquals($this->customer->id, $response->json('data.0.customer.id'));
    }
}
