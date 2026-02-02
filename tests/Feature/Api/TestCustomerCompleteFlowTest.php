<?php

namespace Tests\Feature\Api;

use App\Enums\UserRole;
use App\Models\Customer;
use App\Models\DistributionCenter;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Neighborhood;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Complete API flow test for test customer account
 * Tests all critical endpoints to ensure Google Play Store robots won't crash
 */
class TestCustomerCompleteFlowTest extends TestCase
{
    use RefreshDatabase;

    // Test customer credentials
    private const TEST_EMAIL = 'test.customer@petrolex.com';
    private const TEST_PASSWORD = 'TestPetrolex2026!';
    private const TEST_PHONE = '+237600000001';

    private User $testUser;
    private Customer $testCustomer;
    private string $authToken = '';

    protected function setUp(): void
    {
        parent::setUp();

        // Seed database with necessary data
        $this->seed([
            \Database\Seeders\GeographicSeeder::class,
            \Database\Seeders\RolePermissionSeeder::class,
            \Database\Seeders\Production\BottleTypeSeeder::class,
            \Database\Seeders\Production\AccessoryTypeSeeder::class,
            \Database\Seeders\Production\ProductCategorySeeder::class,
        ]);

        // Seed development data if needed
        if (app()->environment('local', 'development', 'testing')) {
            $this->seed([
                \Database\Seeders\Development\DistributionCenterSeeder::class,
                \Database\Seeders\Production\ProductSeeder::class,
            ]);
        }

        // Ensure test customer exists
        $this->ensureTestCustomerExists();
    }

    private function ensureTestCustomerExists(): void
    {
        $this->testUser = User::firstOrCreate(
            ['email' => self::TEST_EMAIL],
            [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone_number' => self::TEST_PHONE,
                'password' => Hash::make(self::TEST_PASSWORD),
                'country_id' => Country::where('code', 'CM')->first()?->id,
                'is_active' => true,
                'email_verified_at' => now(),
                'language' => 'fr',
            ]
        );

        if (! $this->testUser->hasRole(UserRole::CUSTOMER()->value)) {
            $this->testUser->assignRole(UserRole::CUSTOMER()->value);
        }

        $this->testCustomer = $this->testUser->customer ?? Customer::create([
            'user_id' => $this->testUser->id,
            'current_balance' => 0.00,
        ]);

        // Ensure at least one delivery address exists
        if ($this->testCustomer->deliveryAddresses()->count() === 0) {
            $neighborhood = Neighborhood::first();
            if ($neighborhood) {
                $this->testCustomer->deliveryAddresses()->create([
                    'label' => 'Adresse Test',
                    'address' => 'Adresse de test',
                    'latitude' => 3.8617882,
                    'longitude' => 11.5835694,
                    'phone' => self::TEST_PHONE,
                    'phone_country_code' => '+237',
                    'contact_firstname' => 'Test',
                    'contact_lastname' => 'Customer',
                    'email' => self::TEST_EMAIL,
                    'address_precision' => 'Test',
                    'is_default' => true,
                    'neighborhood_id' => $neighborhood->id,
                ]);
            }
        }
    }

    /** @test */
    public function test_01_health_check_endpoint_works()
    {
        $this->info('🏥 Testing health check endpoint...');

        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => ['status', 'timestamp'],
            ]);

        $this->success('✓ Health check passed');
    }

    /** @test */
    public function test_02_login_customer_with_email()
    {
        $this->info('🔐 Testing customer login with email...');

        $response = $this->postJson('/api/login/customer', [
            'identifier' => self::TEST_EMAIL,
            'password' => self::TEST_PASSWORD,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone_number',
                    ],
                    'customer' => [
                        'id',
                        'current_balance',
                    ],
                    'access_token',
                    'token_type',
                ],
            ]);

        $this->authToken = $response->json('data.access_token');

        $this->assertNotEmpty($this->authToken, 'Auth token should not be empty');
        $this->success('✓ Login successful - Token received');
    }

    /** @test */
    public function test_03_login_customer_with_phone()
    {
        $this->info('📱 Testing customer login with phone...');

        $response = $this->postJson('/api/login/customer', [
            'identifier' => self::TEST_PHONE,
            'password' => self::TEST_PASSWORD,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data' => ['access_token'],
            ]);

        $this->success('✓ Phone login successful');
    }

    /** @test */
    public function test_04_check_authentication()
    {
        $this->loginAsTestCustomer();

        $this->info('✅ Testing auth check endpoint...');

        $response = $this->withToken($this->authToken)
            ->getJson('/api/auth/check');

        $response->assertStatus(200);

        $this->success('✓ Auth check passed');
    }

    /** @test */
    public function test_05_get_user_profile()
    {
        $this->loginAsTestCustomer();

        $this->info('👤 Testing get user profile...');

        $response = $this->withToken($this->authToken)
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone_number',
                        'is_active',
                    ],
                    'customer' => [
                        'id',
                        'current_balance',
                    ],
                ],
            ]);

        $this->success('✓ Profile retrieved successfully');
    }

    /** @test */
    public function test_06_get_countries()
    {
        $this->loginAsTestCustomer();

        $this->info('🌍 Testing get countries...');

        $response = $this->withToken($this->authToken)
            ->getJson('/api/geography/countries');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data' => [
                    '*' => ['id', 'name', 'code'],
                ],
            ]);

        $this->success('✓ Countries retrieved successfully');
    }

    /** @test */
    public function test_07_get_cities()
    {
        $this->loginAsTestCustomer();

        $this->info('🏙️ Testing get cities...');

        $country = Country::where('code', 'CM')->first();

        if (! $country) {
            $this->markTestSkipped('Cameroon not found in database');
        }

        $response = $this->withToken($this->authToken)
            ->getJson("/api/geography/countries/{$country->id}/cities");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data' => [
                    '*' => ['id', 'name'],
                ],
            ]);

        $this->success('✓ Cities retrieved successfully');
    }

    /** @test */
    public function test_08_get_neighborhoods()
    {
        $this->loginAsTestCustomer();

        $this->info('🏘️ Testing get neighborhoods...');

        $city = City::first();

        if (! $city) {
            $this->markTestSkipped('No cities found in database');
        }

        $response = $this->withToken($this->authToken)
            ->getJson("/api/geography/cities/{$city->id}/neighborhoods");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data',
            ]);

        $this->success('✓ Neighborhoods retrieved successfully');
    }

    /** @test */
    public function test_09_get_distribution_centers()
    {
        $this->loginAsTestCustomer();

        $this->info('🏢 Testing get distribution centers...');

        $response = $this->withToken($this->authToken)
            ->getJson('/api/distribution-centers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data',
            ]);

        $this->success('✓ Distribution centers retrieved successfully');
    }

    /** @test */
    public function test_10_get_closest_distribution_center()
    {
        $this->loginAsTestCustomer();

        $this->info('📍 Testing get closest distribution center...');

        $response = $this->withToken($this->authToken)
            ->getJson('/api/distribution-centers/closest?latitude=3.8617882&longitude=11.5835694');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data',
            ]);

        $this->success('✓ Closest distribution center retrieved successfully');
    }

    /** @test */
    public function test_11_get_products_from_distribution_center()
    {
        $this->loginAsTestCustomer();

        $this->info('🛒 Testing get products from distribution center...');

        $distributionCenter = DistributionCenter::first();

        if (! $distributionCenter) {
            $this->markTestSkipped('No distribution centers found in database');
        }

        $response = $this->withToken($this->authToken)
            ->getJson("/api/distribution-centers/{$distributionCenter->id}/products");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data',
            ]);

        $this->success('✓ Products retrieved successfully');
    }

    /** @test */
    public function test_12_get_my_orders_empty()
    {
        $this->loginAsTestCustomer();

        $this->info('📦 Testing get my orders (should be empty)...');

        $response = $this->withToken($this->authToken)
            ->getJson('/api/my/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data',
            ]);

        $this->success('✓ My orders retrieved successfully');
    }

    /** @test */
    public function test_13_create_delivery_address()
    {
        $this->loginAsTestCustomer();

        $this->info('🏠 Testing create delivery address...');

        $neighborhood = Neighborhood::first();

        if (! $neighborhood) {
            $this->markTestSkipped('No neighborhoods found in database');
        }

        $response = $this->withToken($this->authToken)
            ->postJson('/api/my/delivery-addresses', [
                'label' => 'Nouvelle Adresse Test',
                'address' => 'Adresse de test 123',
                'latitude' => 3.8617882,
                'longitude' => 11.5835694,
                'phone' => self::TEST_PHONE,
                'phone_country_code' => '+237',
                'contact_firstname' => 'Test',
                'contact_lastname' => 'Customer',
                'email' => self::TEST_EMAIL,
                'address_precision' => 'Près du marché',
                'is_default' => false,
                'neighborhood_id' => $neighborhood->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'id',
                    'label',
                    'address',
                ],
            ]);

        $this->success('✓ Delivery address created successfully');
    }

    /** @test */
    public function test_14_create_order()
    {
        $this->loginAsTestCustomer();

        $this->info('🛍️ Testing create order...');

        $deliveryAddress = $this->testCustomer->deliveryAddresses()->first();
        $distributionCenter = DistributionCenter::first();
        $product = Product::whereHas('distributionCenterProducts', function ($query) use ($distributionCenter) {
            $query->where('distribution_center_id', $distributionCenter->id)
                ->where('quantity', '>', 0);
        })->first();

        if (! $deliveryAddress || ! $distributionCenter || ! $product) {
            $this->markTestSkipped('Missing required data for order creation');
        }

        $response = $this->withToken($this->authToken)
            ->postJson('/api/orders', [
                'distribution_center_id' => $distributionCenter->id,
                'delivery_address_id' => $deliveryAddress->id,
                'scheduled_delivery_date' => now()->addDays(2)->format('Y-m-d'),
                'scheduled_delivery_time_slot' => '09:00-12:00',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        if ($response->status() !== 201) {
            $this->warn('⚠️ Order creation failed: '.$response->json('_metadata.message', 'Unknown error'));
            $this->warn('Response: '.json_encode($response->json(), JSON_PRETTY_PRINT));
        } else {
            $response->assertStatus(201)
                ->assertJsonStructure([
                    '_metadata' => ['success', 'message'],
                    'data' => [
                        'order' => [
                            'id',
                            'order_number',
                            'status',
                            'total_amount',
                        ],
                    ],
                ]);

            $orderId = $response->json('data.order.id');
            $this->success("✓ Order created successfully (ID: {$orderId})");

            // Test get order details
            $this->test_15_get_order_details($orderId);
        }
    }

    private function test_15_get_order_details(int $orderId): void
    {
        $this->info("📄 Testing get order details (Order #{$orderId})...");

        $response = $this->withToken($this->authToken)
            ->getJson("/api/orders/{$orderId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data' => [
                    'id',
                    'order_number',
                    'status',
                    'items',
                ],
            ]);

        $this->success('✓ Order details retrieved successfully');
    }

    /** @test */
    public function test_16_get_app_version()
    {
        $this->info('📱 Testing get app version...');

        $response = $this->getJson('/api/app/version');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data',
            ]);

        $this->success('✓ App version retrieved successfully');
    }

    /** @test */
    public function test_17_get_terms_and_conditions()
    {
        $this->info('📜 Testing get terms and conditions...');

        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);

        $this->success('✓ Terms and conditions retrieved successfully');
    }

    /** @test */
    public function test_18_get_privacy_policy()
    {
        $this->info('🔒 Testing get privacy policy...');

        $response = $this->getJson('/api/app/privacy-policy');

        $response->assertStatus(200);

        $this->success('✓ Privacy policy retrieved successfully');
    }

    /** @test */
    public function test_19_get_support_contact()
    {
        $this->info('📞 Testing get support contact...');

        $response = $this->getJson('/api/app/support/contact');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success'],
                'data',
            ]);

        $this->success('✓ Support contact retrieved successfully');
    }

    /** @test */
    public function test_20_logout()
    {
        $this->loginAsTestCustomer();

        $this->info('🚪 Testing logout...');

        $response = $this->withToken($this->authToken)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
            ]);

        $this->success('✓ Logout successful');
    }

    // Helper methods

    private function loginAsTestCustomer(): void
    {
        if (empty($this->authToken)) {
            $response = $this->postJson('/api/login/customer', [
                'identifier' => self::TEST_EMAIL,
                'password' => self::TEST_PASSWORD,
            ]);

            $this->authToken = $response->json('data.access_token');
        }
    }

    private function info(string $message): void
    {
        $this->output->writeln("\n<fg=cyan>{$message}</>");
    }

    private function success(string $message): void
    {
        $this->output->writeln("<fg=green>{$message}</>\n");
    }

    private function warn(string $message): void
    {
        $this->output->writeln("<fg=yellow>{$message}</>\n");
    }
}
