<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DistributionCenter;
use App\Models\Geography\Country;
use App\Models\Order;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class OrderTranslationTest extends TestCase
{
    use RefreshDatabase;

    private User $frenchCustomerUser;
    private User $englishCustomerUser;
    private Customer $frenchCustomer;
    private Customer $englishCustomer;
    private CustomerDeliveryAddress $frenchDeliveryAddress;
    private CustomerDeliveryAddress $englishDeliveryAddress;
    private DistributionCenter $distributionCenter;
    private ProductCategory $productCategory;
    private string $frenchAuthToken;
    private string $englishAuthToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);

        // Create country
        $country = Country::factory()->create([
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        // Create French customer user
        $this->frenchCustomerUser = User::factory()->create([
            'country_id' => $country->id,
            'language' => 'fr',
        ]);
        $this->frenchCustomerUser->assignRole('customer');

        // Create English customer user
        $this->englishCustomerUser = User::factory()->create([
            'country_id' => $country->id,
            'language' => 'en',
        ]);
        $this->englishCustomerUser->assignRole('customer');

        // Create French customer
        $this->frenchCustomer = Customer::factory()->create([
            'user_id' => $this->frenchCustomerUser->id,
        ]);

        // Create English customer
        $this->englishCustomer = Customer::factory()->create([
            'user_id' => $this->englishCustomerUser->id,
        ]);

        // Create neighborhood for addresses
        $neighborhood = \App\Models\Geography\Neighborhood::factory()->create();

        // Create French delivery address
        $this->frenchDeliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->frenchCustomer->id,
            'neighborhood_id' => $neighborhood->id,
        ]);

        // Create English delivery address
        $this->englishDeliveryAddress = CustomerDeliveryAddress::factory()->create([
            'customer_id' => $this->englishCustomer->id,
            'neighborhood_id' => $neighborhood->id,
        ]);

        // Create distribution center
        $this->distributionCenter = DistributionCenter::factory()->create([
            'neighborhood_id' => $neighborhood->id,
        ]);

        // Create product category
        $this->productCategory = ProductCategory::factory()->accessoryType()->create();

        // Login French user and get token
        $frenchResponse = $this->postJson(route('api.login'), [
            'login' => $this->frenchCustomerUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $this->frenchAuthToken = $frenchResponse->json('data.access_token');

        // Login English user and get token
        $englishResponse = $this->postJson(route('api.login'), [
            'login' => $this->englishCustomerUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $this->englishAuthToken = $englishResponse->json('data.access_token');
    }

    private function getFrenchOrderData(): array
    {
        // Get the actual price from the product
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $this->productCategory->id
        );
        $quantity = 2;
        $deliveryFee = 500.00;
        $subtotal = $quantity * $actualPrice;
        $totalAmount = $subtotal + $deliveryFee;

        return [
            'delivery_address_id' => $this->frenchDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $this->productCategory->id,
                    'quantity' => $quantity,
                    'unit_price' => $actualPrice,
                    'option' => null,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ];
    }

    private function getEnglishOrderData(): array
    {
        // Get the actual price from the product
        $actualPrice = app(\App\Services\ProductCategoryService::class)->getProductPrice(
            $this->productCategory->id
        );
        $quantity = 2;
        $deliveryFee = 500.00;
        $subtotal = $quantity * $actualPrice;
        $totalAmount = $subtotal + $deliveryFee;

        return [
            'delivery_address_id' => $this->englishDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'delivery_type' => DeliveryType::NORMAL()->value,
            'items' => [
                [
                    'product_category_id' => $this->productCategory->id,
                    'quantity' => $quantity,
                    'unit_price' => $actualPrice,
                    'option' => null,
                ],
            ],
            'delivery_fee' => $deliveryFee,
            'total_amount' => $totalAmount,
        ];
    }

    #[Test]
    public function create_order_returns_french_message_when_user_language_is_french(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->frenchAuthToken,
            'Accept' => 'application/json',
        ])->postJson('/api/orders', $this->getFrenchOrderData());

        $response->assertStatus(201)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Commande créée avec succès. Procédez au paiement.',
                ],
            ]);
    }

    #[Test]
    public function create_order_returns_english_message_when_user_language_is_english(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->englishAuthToken,
            'Accept' => 'application/json',
        ])->postJson('/api/orders', $this->getEnglishOrderData());

        $response->assertStatus(201)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Order created successfully. Proceed to payment.',
                ],
            ]);
    }

    #[Test]
    public function get_order_details_returns_french_message_when_user_language_is_french(): void
    {
        // Create an order
        $order = Order::factory()->create([
            'customer_id' => $this->frenchCustomer->id,
            'delivery_address_id' => $this->frenchDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::PENDING(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->frenchAuthToken,
            'Accept' => 'application/json',
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Détails de la commande récupérés avec succès',
                ],
            ]);
    }

    #[Test]
    public function get_order_details_returns_english_message_when_user_language_is_english(): void
    {
        // Create an order
        $order = Order::factory()->create([
            'customer_id' => $this->englishCustomer->id,
            'delivery_address_id' => $this->englishDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::PENDING(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->englishAuthToken,
            'Accept' => 'application/json',
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Order details retrieved successfully',
                ],
            ]);
    }

    #[Test]
    public function cancel_order_returns_french_message_when_user_language_is_french(): void
    {
        // Create a pending order
        $order = Order::factory()->create([
            'customer_id' => $this->frenchCustomer->id,
            'delivery_address_id' => $this->frenchDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::PENDING(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->frenchAuthToken,
            'Accept' => 'application/json',
        ])->patchJson("/api/orders/{$order->id}/cancel", [
            'cancelled_reason' => 'Test cancellation',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Commande annulée avec succès',
                ],
            ]);
    }

    #[Test]
    public function cancel_order_returns_english_message_when_user_language_is_english(): void
    {
        // Create a pending order
        $order = Order::factory()->create([
            'customer_id' => $this->englishCustomer->id,
            'delivery_address_id' => $this->englishDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::PENDING(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->englishAuthToken,
            'Accept' => 'application/json',
        ])->patchJson("/api/orders/{$order->id}/cancel", [
            'cancelled_reason' => 'Test cancellation',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Order cancelled successfully',
                ],
            ]);
    }

    #[Test]
    public function get_customer_orders_returns_french_message_when_user_language_is_french(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->frenchAuthToken,
            'Accept' => 'application/json',
        ])->getJson('/api/my/orders');

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Commandes client récupérées avec succès',
                ],
            ]);
    }

    #[Test]
    public function get_customer_orders_returns_english_message_when_user_language_is_english(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->englishAuthToken,
            'Accept' => 'application/json',
        ])->getJson('/api/my/orders');

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Customer orders retrieved successfully',
                ],
            ]);
    }

    #[Test]
    public function order_not_belongs_error_returns_french_message_when_user_language_is_french(): void
    {
        // Create another customer's order (English customer's order)
        $order = Order::factory()->create([
            'customer_id' => $this->englishCustomer->id,
            'delivery_address_id' => $this->englishDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::PENDING(),
        ]);

        // Try to access with French user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->frenchAuthToken,
            'Accept' => 'application/json',
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Cette commande ne vous appartient pas',
            ]);
    }

    #[Test]
    public function order_not_belongs_error_returns_english_message_when_user_language_is_english(): void
    {
        // Create another customer's order (French customer's order)
        $order = Order::factory()->create([
            'customer_id' => $this->frenchCustomer->id,
            'delivery_address_id' => $this->frenchDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::PENDING(),
        ]);

        // Try to access with English user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->englishAuthToken,
            'Accept' => 'application/json',
        ])->getJson("/api/orders/{$order->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'This order does not belong to you',
            ]);
    }

    #[Test]
    public function add_customer_comment_returns_french_message_when_user_language_is_french(): void
    {
        // Create a delivered order
        $order = Order::factory()->create([
            'customer_id' => $this->frenchCustomer->id,
            'delivery_address_id' => $this->frenchDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::DELIVERED(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->frenchAuthToken,
            'Accept' => 'application/json',
        ])->postJson("/api/orders/{$order->id}/customer-feedback", [
            'comments' => 'Excellente livraison, très satisfait du service',
            'rating' => 4.5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Commentaire ajouté à la commande avec succès',
                ],
            ]);
    }

    #[Test]
    public function add_customer_comment_returns_english_message_when_user_language_is_english(): void
    {
        // Create a delivered order
        $order = Order::factory()->create([
            'customer_id' => $this->englishCustomer->id,
            'delivery_address_id' => $this->englishDeliveryAddress->id,
            'distribution_center_id' => $this->distributionCenter->id,
            'status' => OrderStatus::DELIVERED(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->englishAuthToken,
            'Accept' => 'application/json',
        ])->postJson("/api/orders/{$order->id}/customer-feedback", [
            'comments' => 'Excellent delivery, very satisfied with the service',
            'rating' => 4.5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Comment added to order successfully',
                ],
            ]);
    }
}
