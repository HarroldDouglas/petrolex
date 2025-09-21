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

/**
 * Test that verifies all Order endpoints return the same uniform structure
 * using OrderDetailResource everywhere.
 */
final class OrderStructureUniformityTest extends TestCase
{
    use RefreshDatabase;

    private User $customerUser;
    private Customer $customer;
    private string $authToken;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Set locale to French for tests
        app()->setLocale('fr');

        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
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
        $loginResponse = $this->postJson(route('api.login'), [
            'login' => $this->customerUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $this->authToken = $loginResponse->json('data.access_token');

        // Create an order for testing
        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
    }

    /**
     * Define the expected uniform structure for all order responses.
     */
    private function getExpectedOrderStructure(): array
    {
        return [
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
            'paid_at',
            'processing_at',
            'delivered_at',
            'cancelled_at',
            'created_at',
            'updated_at',
            'comments',
            'center_comments',
            'rating',
            'cancelled_by',
            'cancelled_reason',
            'customer',
            'delivery_address',
            'delivery_person',
            'distribution_center',
            'payment',
            'items',
            'refunds',
            'delivery_tracking',
            'destination_coordinates',
            'bottle_info',
            'invoice_url',
        ];
    }

    /**
     * Define the expected uniform structure for delivery_address object.
     */
    private function getExpectedDeliveryAddressStructure(): array
    {
        return [
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
        ];
    }

    #[Test]
    public function order_details_endpoint_returns_uniform_structure(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $this->order->id]));

        $response->assertStatus(200);

        $orderData = $response->json('data');
        $this->assertIsArray($orderData);

        // Verify all expected fields are present
        foreach ($this->getExpectedOrderStructure() as $field) {
            $this->assertArrayHasKey($field, $orderData, "Missing field: {$field} in order details response");
        }

        // If delivery_address is present, verify its structure
        if (! is_null($orderData['delivery_address'])) {
            foreach ($this->getExpectedDeliveryAddressStructure() as $field) {
                $this->assertArrayHasKey($field, $orderData['delivery_address'], "Missing field: {$field} in delivery_address");
            }
        }
    }

    #[Test]
    public function customer_orders_list_returns_uniform_structure(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index'));

        $response->assertStatus(200);

        $ordersData = $response->json('data');
        $this->assertIsArray($ordersData);

        if (count($ordersData) > 0) {
            $firstOrder = $ordersData[0];

            // Verify all expected fields are present
            foreach ($this->getExpectedOrderStructure() as $field) {
                $this->assertArrayHasKey($field, $firstOrder, "Missing field: {$field} in customer orders list response");
            }

            // If delivery_address is present, verify its structure
            if (! is_null($firstOrder['delivery_address'])) {
                foreach ($this->getExpectedDeliveryAddressStructure() as $field) {
                    $this->assertArrayHasKey($field, $firstOrder['delivery_address'], "Missing field: {$field} in delivery_address");
                }
            }
        }
    }

    #[Test]
    public function create_order_endpoint_returns_uniform_structure(): void
    {
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get actual price to avoid validation errors
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice($productCategory->id);
        $quantity = 2;
        $deliveryFee = 500.00;
        $totalAmount = ($quantity * $actualPrice) + $deliveryFee;

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => \App\Enums\DeliveryType::NORMAL()->value,
            'payment_method' => \App\Enums\PaymentMethod::ORANGE_MONEY()->value,
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
            'comments' => 'Test order creation',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), $orderData);

        $response->assertStatus(201);

        $orderData = $response->json('data.order');
        $this->assertIsArray($orderData);

        // Verify all expected fields are present
        foreach ($this->getExpectedOrderStructure() as $field) {
            $this->assertArrayHasKey($field, $orderData, "Missing field: {$field} in create order response");
        }

        // If delivery_address is present, verify its structure
        if (! is_null($orderData['delivery_address'])) {
            foreach ($this->getExpectedDeliveryAddressStructure() as $field) {
                $this->assertArrayHasKey($field, $orderData['delivery_address'], "Missing field: {$field} in delivery_address");
            }
        }
    }

    #[Test]
    public function all_order_endpoints_have_identical_structure(): void
    {
        // Create an order for testing create endpoint
        $distributionCenter = \App\Models\DistributionCenter::factory()->create();
        $deliveryAddress = \App\Models\CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->customer->id,
        ]);
        $productCategory = \App\Models\ProductCategory::factory()->accessoryType()->create();

        // Get actual price to avoid validation errors
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice($productCategory->id);
        $quantity = 1;
        $deliveryFee = 500.00;
        $totalAmount = ($quantity * $actualPrice) + $deliveryFee;

        $orderData = [
            'delivery_address_id' => $deliveryAddress->id,
            'distribution_center_id' => $distributionCenter->id,
            'delivery_type' => \App\Enums\DeliveryType::NORMAL()->value,
            'payment_method' => \App\Enums\PaymentMethod::ORANGE_MONEY()->value,
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
            'comments' => 'Test uniformity',
        ];

        // Test create order structure
        $createResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->postJson(route('api.orders.store'), $orderData);

        $createResponse->assertStatus(201);
        $createdOrder = $createResponse->json('data.order');
        $createdOrderId = $createdOrder['id'];

        // Test order details structure
        $detailsResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $createdOrderId]));

        $detailsResponse->assertStatus(200);
        $orderDetails = $detailsResponse->json('data');

        // Test customer orders list structure
        $listResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index'));

        $listResponse->assertStatus(200);
        $ordersList = $listResponse->json('data');

        // Find our created order in the list
        $listOrder = collect($ordersList)->firstWhere('id', $createdOrderId);
        $this->assertNotNull($listOrder, 'Created order not found in customer orders list');

        // Compare structures - they should have exactly the same keys
        $createKeys = array_keys($createdOrder);
        $detailsKeys = array_keys($orderDetails);
        $listKeys = array_keys($listOrder);

        sort($createKeys);
        sort($detailsKeys);
        sort($listKeys);

        $this->assertEquals($createKeys, $detailsKeys, 'Create and Details endpoints have different structures');
        $this->assertEquals($createKeys, $listKeys, 'Create and List endpoints have different structures');
        $this->assertEquals($detailsKeys, $listKeys, 'Details and List endpoints have different structures');

        // Verify delivery_address structure consistency
        $this->verifyDeliveryAddressUniformity($createdOrder, $orderDetails, $listOrder);

        // Verify distribution_center structure consistency
        $this->verifyDistributionCenterUniformity($createdOrder, $orderDetails, $listOrder);

        // Verify customer structure consistency
        $this->verifyCustomerUniformity($createdOrder, $orderDetails, $listOrder);
    }

    #[Test]
    public function details_and_list_endpoints_have_identical_structure(): void
    {
        // Test order details structure
        $detailsResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.orders.show', ['order' => $this->order->id]));

        $detailsResponse->assertStatus(200);
        $orderDetails = $detailsResponse->json('data');

        // Test customer orders list structure
        $listResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.my.orders.index'));

        $listResponse->assertStatus(200);
        $ordersList = $listResponse->json('data');

        if (count($ordersList) > 0) {
            $listOrder = $ordersList[0];

            // Compare structures - they should have exactly the same keys
            $detailsKeys = array_keys($orderDetails);
            $listKeys = array_keys($listOrder);

            sort($detailsKeys);
            sort($listKeys);

            $this->assertEquals($detailsKeys, $listKeys, 'Details and List endpoints have different structures');

            // Verify delivery_address structure consistency if present
            if (! is_null($orderDetails['delivery_address']) &&
                ! is_null($listOrder['delivery_address'])) {

                $detailsDeliveryKeys = array_keys($orderDetails['delivery_address']);
                $listDeliveryKeys = array_keys($listOrder['delivery_address']);

                sort($detailsDeliveryKeys);
                sort($listDeliveryKeys);

                $this->assertEquals($detailsDeliveryKeys, $listDeliveryKeys, 'Delivery address structure differs between Details and List');
            }
        }
    }

    /**
     * Verify that delivery_address has consistent structure across all endpoints
     */
    private function verifyDeliveryAddressUniformity(array $createOrder, array $detailsOrder, array $listOrder): void
    {
        $deliveryAddresses = [
            'create' => $createOrder['delivery_address'] ?? null,
            'details' => $detailsOrder['delivery_address'] ?? null,
            'list' => $listOrder['delivery_address'] ?? null,
        ];

        $nonNullDeliveryAddresses = array_filter($deliveryAddresses);

        if (count($nonNullDeliveryAddresses) > 1) {
            $keys = array_map('array_keys', $nonNullDeliveryAddresses);
            $firstKeys = array_shift($keys);
            sort($firstKeys);

            foreach ($keys as $endpointKeys) {
                sort($endpointKeys);
                $this->assertEquals($firstKeys, $endpointKeys, 'Delivery address structure differs between endpoints');
            }
        }
    }

    /**
     * Verify that distribution_center has consistent structure across all endpoints
     */
    private function verifyDistributionCenterUniformity(array $createOrder, array $detailsOrder, array $listOrder): void
    {
        $distributionCenters = [
            'create' => $createOrder['distribution_center'] ?? null,
            'details' => $detailsOrder['distribution_center'] ?? null,
            'list' => $listOrder['distribution_center'] ?? null,
        ];

        $nonNullDistributionCenters = array_filter($distributionCenters);

        if (count($nonNullDistributionCenters) > 1) {
            $keys = array_map('array_keys', $nonNullDistributionCenters);
            $firstKeys = array_shift($keys);
            sort($firstKeys);

            foreach ($keys as $endpointKeys) {
                sort($endpointKeys);
                $this->assertEquals($firstKeys, $endpointKeys, 'Distribution center structure differs between endpoints');
            }
        }
    }

    /**
     * Verify that customer has consistent structure across all endpoints
     */
    private function verifyCustomerUniformity(array $createOrder, array $detailsOrder, array $listOrder): void
    {
        $customers = [
            'create' => $createOrder['customer'] ?? null,
            'details' => $detailsOrder['customer'] ?? null,
            'list' => $listOrder['customer'] ?? null,
        ];

        $nonNullCustomers = array_filter($customers);

        if (count($nonNullCustomers) > 1) {
            $keys = array_map('array_keys', $nonNullCustomers);
            $firstKeys = array_shift($keys);
            sort($firstKeys);

            foreach ($keys as $endpointKeys) {
                sort($endpointKeys);
                $this->assertEquals($firstKeys, $endpointKeys, 'Customer structure differs between endpoints');
            }
        }
    }
}
