<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\OrderBottleScans;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BottleVerificationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;
    private DistributionCenter $distributionCenter;
    private BottleType $bottleType;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $country = \App\Models\Geography\Country::where('code', 'CM')->first();
        if (!$country) {
            $country = \App\Models\Geography\Country::create([
                'name' => 'Cameroun',
                'code' => 'CM',
                'phone_code' => '+237',
                'is_active' => true,
            ]);
        }

        $this->adminUser = User::factory()->create([
            'password' => \Illuminate\Support\Facades\Hash::make('password'), // Use Hash::make
            'country_id' => $country->id,
        ]);
        $this->adminUser->assignRole('admin');

        \App\Models\Customer::factory()->create();

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $response->assertStatus(200);
        $this->authToken = $response->json('data.access_token');

        $this->distributionCenter = DistributionCenter::factory()->create();
        $this->bottleType = BottleType::factory()->create();
        ProductCategory::factory()->create();
        Product::factory()->create();
    }

    #[Test]
    public function it_can_verify_an_authentic_with_delivery_person_filled_bottle_and_matching_distribution_center(): void
    {
        $bottle = Bottle::factory()->create([
            'barcode' => 'TESTBARCODE1',
            'status' => BottleStatus::WITH_DELIVERY_PERSON(),
            'is_filled' => true,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $order = Order::factory()->create([
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $productCategory = ProductCategory::factory()->create([
            'product_type' => \App\Enums\ProductType::BOTTLE(),
            'product_type_id' => $this->bottleType->id,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_category_id' => $productCategory->id,
            'quantity' => 1,
            'unit_price' => 1000,
            'total_price' => 1000,
        ]);
        $orderItem->bottles()->attach($bottle->id);

        OrderBottleScans::firstOrCreate([
            'order_item_id' => $orderItem->id,
            'bottle_id' => $bottle->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.bottles.verify', [
            'barcode' => $bottle->barcode,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.authentic', true);
    }

    #[Test]
    public function it_returns_not_authentic_for_non_existent_barcode(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.bottles.verify', ['barcode' => 'NONEXISTENT']));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.authentic', false);
    }

    #[Test]
    public function it_returns_not_authentic_for_unfilled_bottle(): void
    {
        $bottle = Bottle::factory()->create([
            'barcode' => 'TESTBARCODE3',
            'status' => BottleStatus::IN_STOCK(),
            'is_filled' => false,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.bottles.verify', ['barcode' => $bottle->barcode]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.authentic', false);
    }

    #[Test]
    public function it_returns_not_authentic_for_bottle_with_wrong_status(): void
    {
        $bottle = Bottle::factory()->create([
            'barcode' => 'TESTBARCODE4',
            'status' => BottleStatus::WITH_CLIENT(),
            'is_filled' => true,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.bottles.verify', ['barcode' => $bottle->barcode]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.authentic', false);
    }

    #[Test]
    public function it_returns_401_for_unauthenticated_access(): void
    {
        $response = $this->getJson(route('api.bottles.verify', ['barcode' => 'ANYBARCODE']));

        $response->assertStatus(401);
    }
}
