<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\DistributionCenter;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DistributionCenterProductsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create an admin user and authenticate to get a token
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        // Create a product category for products
        \App\Models\ProductCategory::factory()->create();

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password', // Default password from factory
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_retrieve_a_list_of_products_for_a_distribution_center(): void
    {
        $distributionCenter = DistributionCenter::factory()->create();

        // Create product categories and attach them to the distribution center
        $productCategories = \App\Models\ProductCategory::factory()->count(5)->create();
        foreach ($productCategories as $productCategory) {
            $distributionCenter->productCategories()->attach($productCategory->id, ['stock' => 10, 'stock_empty' => 5, 'stock_filled' => 5]);
        }

        // Ensure products are created for these categories
        foreach ($productCategories as $productCategory) {
            Product::factory()->create(['product_category_id' => $productCategory->id]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.products', ['id' => $distributionCenter->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'name',
                        'description',
                        'quantity',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonCount(5, 'data');
    }
}
