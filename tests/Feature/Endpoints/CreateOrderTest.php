<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;
    private Customer $customer;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Set locale to French for tests
        app()->setLocale('fr');

        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

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

        $this->authToken = $response->json('data.access_token');
    }

    #[Test]
    public function it_can_create_order_successfully(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get the actual price from the product
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $productCategory->id
        );
        $quantity = 2;
        $deliveryFee = 500.00;
        $subtotal = $quantity * $actualPrice;
        $totalAmount = $subtotal + $deliveryFee;

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => $quantity,
                    'unit_price' => $actualPrice,
                    'option' => null,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
            'comments' => 'Livrer avant 18h',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'order' => [
                        'id',
                        'order_number',
                        'status',
                        'status_label',
                        'delivery_type',
                        'delivery_type_label',
                        'subtotal',
                        'delivery_fee',
                        'total_amount',
                        'comments',
                        'customer',
                        'delivery_address',
                        'items',
                        'invoice_url',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.order.status', OrderStatus::PENDING()->value)
            ->assertJsonPath('data.order.delivery_type', DeliveryType::NORMAL()->value)
            ->assertJsonPath('data.order.comments', 'Livrer avant 18h');

        // Verify order was created in database
        $this->assertDatabaseHas('orders', [
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'status' => OrderStatus::PENDING()->value,
            'comments' => 'Livrer avant 18h',
        ]);

        // Verify order items were created
        $this->assertDatabaseHas('order_items', [
            'product_category_id' => $productCategory->id,
            'quantity' => 2,
        ]);

    }

    #[Test]
    public function it_validates_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'delivery_address_id',
                    'distribution_center_id',
                    'delivery_type',
                    'items',
                    'delivery_fee',
                    'total_amount',
                ],
            ]);
    }

    #[Test]
    public function it_validates_delivery_address_belongs_to_customer(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Create delivery address for another customer
        $otherCustomer = Customer::factory()->create();
        $otherDeliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        // Get actual price
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $productCategory->id
        );
        $deliveryFee = 500.00;
        $totalAmount = $actualPrice + $deliveryFee;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $otherDeliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                    'unit_price' => $actualPrice,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'delivery_address_id',
                ],
            ]);
    }

    #[Test]
    public function it_validates_items_quantity_limits(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get actual price
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $productCategory->id
        );
        $deliveryFee = 500.00;

        // Test quantity too low
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 0,
                    'unit_price' => $actualPrice,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $deliveryFee, // 0 * price + delivery fee
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'items.0.quantity',
                ],
            ]);

        // Test quantity too high
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 101,
                    'unit_price' => $actualPrice,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => (101 * $actualPrice) + $deliveryFee,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'items.0.quantity',
                ],
            ]);
    }

    #[Test]
    public function it_validates_comments_length(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get actual price
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $productCategory->id
        );
        $deliveryFee = 500.00;
        $totalAmount = $actualPrice + $deliveryFee;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                    'unit_price' => $actualPrice,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
            'comments' => str_repeat('a', 501), // Too long
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'comments',
                ],
            ]);
    }

    #[Test]
    public function it_requires_authentication(): void
    {
        $response = $this->postJson(route('api.orders.store'), []);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_requires_customer_role(): void
    {
        // Create non-customer user
        $adminUser = User::factory()->create();

        $response = $this->postJson(route('api.login'), [
            'login' => $adminUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $adminToken = $response->json('data.access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$adminToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), []);

        $response->assertStatus(403);
    }

    #[Test]
    public function it_calculates_order_amounts_correctly(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get actual price
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $productCategory->id
        );
        $quantity = 2;
        $deliveryFee = 500.00;
        $subtotal = $quantity * $actualPrice;
        $totalAmount = $subtotal + $deliveryFee;

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => $quantity,
                    'unit_price' => $actualPrice,
                    'option' => null,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), $orderData);

        $response->assertStatus(201);

        $orderData = $response->json('data.order');

        // Verify amounts are present and valid
        $this->assertIsNumeric($orderData['subtotal']);
        $this->assertIsNumeric($orderData['delivery_fee']);
        $this->assertIsNumeric($orderData['total_amount']);

        // Verify calculation: total = subtotal + delivery_fee
        $this->assertEqualsWithDelta(
            $orderData['subtotal'] + $orderData['delivery_fee'],
            $orderData['total_amount'],
            0.01
        );
    }

    #[Test]
    public function it_validates_geographic_coherence_same_municipality(): void
    {
        // Create a city
        $city = \App\Models\Geography\City::factory()->create();

        // Create a municipality
        $municipality = \App\Models\Geography\Municipality::factory()->create([
            'city_id' => $city->id,
        ]);

        // Create a neighborhood in this municipality
        $neighborhood = \App\Models\Geography\Neighborhood::factory()->create([
            'municipality_id' => $municipality->id,
        ]);

        // Create distribution center in this neighborhood
        $distributionCenter = \App\Models\DistributionCenter::factory()->create([
            'neighborhood_id' => $neighborhood->id,
        ]);

        // Create delivery address in the SAME neighborhood (same municipality)
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $neighborhood->id,
        ]);

        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get actual price
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $productCategory->id
        );
        $deliveryFee = 500.00;
        $totalAmount = $actualPrice + $deliveryFee;

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                    'unit_price' => $actualPrice,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), $orderData);

        // Should succeed because both are in the same municipality
        $response->assertStatus(201)
            ->assertJsonPath('_metadata.success', true);
    }

    #[Test]
    public function it_rejects_order_when_delivery_address_and_center_in_different_municipalities(): void
    {
        // Create a city
        $city = \App\Models\Geography\City::factory()->create();

        // Create two different municipalities in the same city
        $municipality1 = \App\Models\Geography\Municipality::factory()->create([
            'city_id' => $city->id,
            'name' => 'Yaoundé I',
        ]);

        $municipality2 = \App\Models\Geography\Municipality::factory()->create([
            'city_id' => $city->id,
            'name' => 'Yaoundé II',
        ]);

        // Create neighborhoods in different municipalities
        $neighborhood1 = \App\Models\Geography\Neighborhood::factory()->create([
            'municipality_id' => $municipality1->id,
        ]);

        $neighborhood2 = \App\Models\Geography\Neighborhood::factory()->create([
            'municipality_id' => $municipality2->id,
        ]);

        // Create distribution center in municipality 1
        $distributionCenter = \App\Models\DistributionCenter::factory()->create([
            'neighborhood_id' => $neighborhood1->id,
        ]);

        // Create delivery address in municipality 2 (DIFFERENT municipality)
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
            'neighborhood_id' => $neighborhood2->id,
        ]);

        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get actual price
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $productCategory->id
        );
        $deliveryFee = 500.00;
        $totalAmount = $actualPrice + $deliveryFee;

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                    'unit_price' => $actualPrice,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), $orderData);

        // Should fail with 422 validation error
        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'distribution_center_id',
                ],
            ]);

        // Verify error message mentions municipalities or contains validation message
        $errorMessage = $response->json('errors.distribution_center_id.0');

        // Check if the error message contains municipality names OR is the translated message
        $isValid = str_contains($errorMessage, 'Yaoundé I') && str_contains($errorMessage, 'Yaoundé II')
            || str_contains($errorMessage, 'municipalité')
            || str_contains($errorMessage, 'municipality');

        $this->assertTrue($isValid, "Error message should mention municipalities: {$errorMessage}");
    }
}
