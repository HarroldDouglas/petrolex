<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
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

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 2,
                    'option' => null,
                ],
            ],
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
                    'payment' => [
                        'payment_reference',
                        'payment_status',
                        'payment_status_label',
                        'payment_method',
                        'payment_method_label',
                        'amount_due',
                        'amount_paid',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.order.status', OrderStatus::PENDING()->value)
            ->assertJsonPath('data.order.delivery_type', DeliveryType::NORMAL()->value)
            ->assertJsonPath('data.order.comments', 'Livrer avant 18h')
            ->assertJsonPath('data.payment.payment_status', PaymentStatus::PENDING()->value)
            ->assertJsonPath('data.payment.payment_method', PaymentMethod::ORANGE_MONEY()->value);

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

        // Verify payment was created
        $this->assertDatabaseHas('order_payments', [
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'payment_status' => PaymentStatus::PENDING()->value,
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
                    'payment_method',
                    'items',
                ],
            ]);
    }

    #[Test]
    public function it_validates_delivery_type_enum_values(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => 'invalid_type',
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $actualMessage = $response->json('errors.delivery_type.0');
        $this->assertStringContainsString('normal', $actualMessage);
        $this->assertStringContainsString('fast', $actualMessage);
    }

    #[Test]
    public function it_validates_payment_method_enum_values(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => 'invalid_method',
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(422);
        $actualMessage = $response->json('errors.payment_method.0');
        $this->assertStringContainsString('orange_money', $actualMessage);
        $this->assertStringContainsString('mtn_money', $actualMessage);
        $this->assertStringContainsString('credit_card', $actualMessage);
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

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $otherDeliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                ],
            ],
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

        // Test quantity too low
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 0,
                ],
            ],
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
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 101,
                ],
            ],
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

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 1,
                ],
            ],
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

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'payment_method' => PaymentMethod::ORANGE_MONEY()->value,
            'items' => [
                [
                    'product_category_id' => $productCategory->id,
                    'quantity' => 2,
                    'option' => null,
                ],
            ],
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
}
