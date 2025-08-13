<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\BottleStatus;
use App\Models\Bottle;
use App\Models\BottleType;
use App\Models\DistributionCenter;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');

        $this->distributionCenter = DistributionCenter::factory()->create();
        $this->bottleType = BottleType::factory()->create();
        ProductCategory::factory()->create();
        Product::factory()->create();
    }

    /** @test */
    public function it_can_verify_an_authentic_in_stock_filled_bottle(): void
    {
        $bottle = Bottle::factory()->create([
            'barcode' => 'TESTBARCODE1',
            'status' => BottleStatus::IN_STOCK(),
            'is_filled' => true,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.bottles.verify', ['barcode' => $bottle->barcode]));

        $response->dump();

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.authentic', true);
        // ->assertJsonPath('data.status', BottleStatus::IN_STOCK()->value);
    }

    /** @test */
    public function it_can_verify_an_authentic_with_delivery_person_filled_bottle(): void
    {
        $bottle = Bottle::factory()->create([
            'barcode' => 'TESTBARCODE2',
            'status' => BottleStatus::WITH_DELIVERY_PERSON(),
            'is_filled' => true,
            'distribution_center_id' => $this->distributionCenter->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.bottles.verify', ['barcode' => $bottle->barcode]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.authentic', true)
            ->assertJsonPath('data.status', BottleStatus::WITH_DELIVERY_PERSON()->value);
    }

    /** @test */
    public function it_returns_not_authentic_for_non_existent_barcode(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.bottles.verify', ['barcode' => 'NONEXISTENT']));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.authentic', false)
            ->assertJsonPath('data.status', null);
    }

    /** @test */
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
            ->assertJsonPath('data.authentic', false)
            ->assertJsonPath('data.status', null);
    }

    /** @test */
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
            ->assertJsonPath('data.authentic', false)
            ->assertJsonPath('data.status', null);
    }

    /** @test */
    public function it_returns_401_for_unauthenticated_access(): void
    {
        $response = $this->getJson(route('api.bottles.verify', ['barcode' => 'ANYBARCODE']));

        $response->assertStatus(401);
    }
}
