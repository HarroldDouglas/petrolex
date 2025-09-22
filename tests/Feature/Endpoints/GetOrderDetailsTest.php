<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Customer;
use App\Models\Geography\Country;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GetOrderDetailsTest extends TestCase
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
    public function it_can_get_order_details_successfully(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $order->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'status_label',
                    'delivery_type',
                    'delivery_type_label',
                    'subtotal',
                    'delivery_fee',
                    'total_amount',
                    'total_refunded_amount',
                    'order_date',
                    'delivery_date',
                    'created_at',
                    'updated_at',
                    'customer' => [
                        'id',
                        'first_name',
                        'last_name',
                        'full_name',
                        'email',
                        'phone_number',
                        'customer_id',
                        'current_balance',
                        'delivery_addresses' => [
                            '*' => [
                                'id',
                                'label',
                                'address',
                                'neighborhood',
                                'municipality',
                                'city',
                                'country',
                                'latitude',
                                'longitude',
                                'phone',
                                'phone_country_code',
                                'contact_firstname',
                                'contact_lastname',
                                'contact_full_name',
                                'email',
                                'address_precision',
                                'is_default',
                            ],
                        ],
                    ],
                    'delivery_address' => [
                        'id',
                        'label',
                        'address',
                        'neighborhood',
                        'municipality',
                        'city',
                        'country',
                        'latitude',
                        'longitude',
                        'phone',
                        'phone_country_code',
                        'contact_firstname',
                        'contact_lastname',
                        'contact_full_name',
                        'email',
                        'address_precision',
                        'is_default',
                    ],
                    'delivery_person',
                    'distribution_center',
                    'payment',
                    'items',
                    'refunds',
                    'delivery_tracking',
                    'destination_coordinates' => ['latitude', 'longitude'],
                    'bottle_info' => [
                        'has_bottle_items',
                        'has_refunds',
                        'all_bottles_scanned',
                        'bottle_scan_progress',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.order_number', $order->order_number);
    }

    #[Test]
    public function it_ensures_delivery_addresses_have_no_null_geographic_data(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $order->id]));

        $response->assertStatus(200);

        $data = $response->json('data');

        // Verify delivery_address has complete geographic data
        $this->assertNotNull($data['delivery_address']['neighborhood']);
        $this->assertNotNull($data['delivery_address']['municipality']);
        $this->assertNotNull($data['delivery_address']['city']);
        $this->assertNotNull($data['delivery_address']['country']);

        // Verify customer delivery_addresses have complete geographic data
        foreach ($data['customer']['delivery_addresses'] as $address) {
            $this->assertNotNull($address['neighborhood'], 'Neighborhood should not be null');
            $this->assertNotNull($address['municipality'], 'Municipality should not be null');
            $this->assertNotNull($address['city'], 'City should not be null');
            $this->assertNotNull($address['country'], 'Country should not be null');

            // Verify country has currency info (can be null if no currency is set)
            if (isset($address['country']['currency'])) {
                $this->assertNotNull($address['country']['decimal_places']);
            }
        }
    }

    #[Test]
    public function it_returns_unauthorized_error_for_unauthenticated_user(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create();

        $order = Order::factory()->create([
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        $response = $this->getJson(route('api.orders.show', ['order' => $order->id]));

        $response->assertStatus(401);
    }

    #[Test]
    public function it_returns_not_found_error_for_nonexistent_order(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => 99999]));

        $response->assertStatus(404);
    }

    #[Test]
    public function it_can_access_order_with_all_relations_loaded(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Create order items
        $productCategory = \App\Models\ProductCategory::factory()->create();
        \App\Models\OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_category_id' => $productCategory->id,
        ]);

        // Create payment
        \App\Models\OrderPayment::factory()->create([
            'order_id' => $order->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $order->id]));

        $response->assertStatus(200);

        $data = $response->json('data');

        // Verify all main relations are loaded
        $this->assertNotNull($data['customer']);
        $this->assertNotNull($data['delivery_address']);
        $this->assertNotNull($data['distribution_center']);
        $this->assertNotNull($data['payment']);
        $this->assertNotEmpty($data['items']);

        // Verify nested relations are loaded
        $this->assertNotNull($data['items'][0]['product_category']);
        $this->assertNotNull($data['customer']['delivery_addresses']);
    }

    #[Test]
    public function it_denies_access_to_orders_belonging_to_other_customers(): void
    {
        // Create another customer
        $otherCustomerUser = User::factory()->create();
        $otherCustomerUser->assignRole('customer');
        $otherCustomer = Customer::factory()->create([
            'user_id' => $otherCustomerUser->id,
        ]);

        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        // Create order belonging to other customer
        $otherOrder = Order::factory()->create([
            'customer_id' => $otherCustomer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Try to access other customer's order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $otherOrder->id]));

        $response->assertStatus(403)
            ->assertJsonPath('message', __('api.order_not_belongs_to_you'));
    }

    #[Test]
    public function it_allows_delivery_persons_to_access_any_order(): void
    {
        // Create delivery person role and user
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'delivery_person', 'guard_name' => 'web']);
        $deliveryUser = User::factory()->create();
        $deliveryUser->assignRole('delivery_person');

        // Login delivery person
        $response = $this->postJson(route('api.login'), [
            'login' => $deliveryUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $deliveryToken = $response->json('data.access_token');

        // Create order belonging to our customer
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_address_id' => $deliveryAddress->id,
        ]);

        // Delivery person should be able to access any order
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$deliveryToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $order->id]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.id', $order->id);
    }
}
