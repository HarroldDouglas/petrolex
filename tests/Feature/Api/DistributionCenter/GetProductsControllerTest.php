<?php

namespace Tests\Feature\Api\DistributionCenter;

use App\Enums\Language;
use App\Models\DistributionCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetProductsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected DistributionCenter $distributionCenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create([
            'email' => 'test@example.com',
            'language' => Language::FRENCH(),
        ]);

        $this->distributionCenter = DistributionCenter::factory()->create();
    }

    public function test_user_can_get_products_with_french_translations(): void
    {

        $this->customer->language = Language::FRENCH();
        $this->customer->save();

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/distribution-centers/{$this->distributionCenter->id}/products");

        $response->assertSuccessful();
        $response->assertJsonStructure([
            '_metadata' => [
                'success',
                'message',
            ],
            'data' => [
                '*' => [
                    'id',
                    'type',
                    'name',
                    'description',
                    'category_name',
                    'quantity',
                    'specifications' => [
                        '*' => [
                            'name',
                            'value',
                            'unit',
                        ],
                    ],
                    'images' => [
                        '*' => [
                            'url',
                            'thumb',
                            'medium',
                            'large',
                            'is_default',
                        ],
                    ],
                    'options' => [
                        '*' => [
                            'value',
                            'label',
                            'price',
                            'is_default',
                        ],
                    ],
                ],
            ],
        ]);

        $products = $response->json('data');
        if (! empty($products)) {
            $bottleProducts = collect($products)->filter(fn ($product) => $product['type'] === 'bottle');
            $accessoryProducts = collect($products)->filter(fn ($product) => $product['type'] === 'accessory');

            if ($bottleProducts->isNotEmpty()) {
                $this->assertEquals('Bouteilles à gaz domestiques', $bottleProducts->first()['category_name']);

                $bottleProduct = $bottleProducts->first();
                $this->assertIsArray($bottleProduct['specifications']);
                if (! empty($bottleProduct['specifications'])) {
                    $specification = $bottleProduct['specifications'][0];
                    $this->assertArrayHasKey('name', $specification);
                    $this->assertArrayHasKey('value', $specification);

                    $specificationNames = collect($bottleProduct['specifications'])->pluck('name')->toArray();
                    $expectedFrenchNames = ['Capacité', 'Hauteur', 'Poids', 'Rayon'];
                    $this->assertTrue(
                        ! empty(array_intersect($specificationNames, $expectedFrenchNames)),
                        'Expected French specification names not found. Found: '.implode(', ', $specificationNames)
                    );
                }
            }

            if ($accessoryProducts->isNotEmpty()) {
                $this->assertEquals('Accessoires de sécurité et distributions', $accessoryProducts->first()['category_name']);

                $accessoryProduct = $accessoryProducts->first();
                $this->assertIsArray($accessoryProduct['specifications']);
                if (! empty($accessoryProduct['specifications'])) {
                    $specification = $accessoryProduct['specifications'][0];
                    $this->assertArrayHasKey('name', $specification);
                    $this->assertArrayHasKey('value', $specification);
                }
            }
        }

        $this->assertEquals('Produits récupérés avec succès', $response->json('_metadata.message'));
    }

    public function test_user_can_get_products_with_english_translations(): void
    {
        $this->customer->language = Language::ENGLISH();
        $this->customer->save();

        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/distribution-centers/{$this->distributionCenter->id}/products");

        $response->assertSuccessful();

        $products = $response->json('data');
        if (! empty($products)) {
            $bottleProducts = collect($products)->filter(fn ($product) => $product['type'] === 'bottle');
            $accessoryProducts = collect($products)->filter(fn ($product) => $product['type'] === 'accessory');

            if ($bottleProducts->isNotEmpty()) {
                $this->assertEquals('Domestic Gas Bottles', $bottleProducts->first()['category_name']);

                $bottleProduct = $bottleProducts->first();
                $this->assertIsArray($bottleProduct['specifications']);
                if (! empty($bottleProduct['specifications'])) {
                    $specification = $bottleProduct['specifications'][0];
                    $this->assertArrayHasKey('name', $specification);
                    $this->assertArrayHasKey('value', $specification);

                    $specificationNames = collect($bottleProduct['specifications'])->pluck('name')->toArray();
                    $expectedEnglishNames = ['Capacity', 'Height', 'Weight', 'Radius'];
                    $this->assertTrue(
                        ! empty(array_intersect($specificationNames, $expectedEnglishNames)),
                        'Expected English specification names not found. Found: '.implode(', ', $specificationNames)
                    );
                }
            }

            if ($accessoryProducts->isNotEmpty()) {
                $this->assertEquals('Safety and Distribution Accessories', $accessoryProducts->first()['category_name']);

                $accessoryProduct = $accessoryProducts->first();
                $this->assertIsArray($accessoryProduct['specifications']);
                if (! empty($accessoryProduct['specifications'])) {
                    $specification = $accessoryProduct['specifications'][0];
                    $this->assertArrayHasKey('name', $specification);
                    $this->assertArrayHasKey('value', $specification);
                }
            }
        }
    }

    public function test_unauthenticated_user_cannot_access_products(): void
    {

        $response = $this->getJson("/api/distribution-centers/{$this->distributionCenter->id}/products");

        $response->assertUnauthorized();
    }

    public function test_returns_404_for_non_existent_distribution_center(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson('/api/distribution-centers/999/products');

        $response->assertNotFound();
    }

    public function test_products_include_images_array(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/distribution-centers/{$this->distributionCenter->id}/products");

        $response->assertSuccessful();

        $products = $response->json('data');
        if (! empty($products)) {
            foreach ($products as $product) {
                $this->assertArrayHasKey('images', $product);
                $this->assertIsArray($product['images']);

                foreach ($product['images'] as $image) {
                    $this->assertArrayHasKey('url', $image);
                    $this->assertArrayHasKey('thumb', $image);
                    $this->assertArrayHasKey('medium', $image);
                    $this->assertArrayHasKey('large', $image);
                    $this->assertArrayHasKey('is_default', $image);
                    $this->assertIsBool($image['is_default']);
                }
            }
        }
    }

    public function test_products_include_options_array(): void
    {
        $response = $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/distribution-centers/{$this->distributionCenter->id}/products");

        $response->assertSuccessful();

        $products = $response->json('data');
        if (! empty($products)) {
            foreach ($products as $product) {
                $this->assertArrayHasKey('options', $product);
                $this->assertIsArray($product['options']);

                foreach ($product['options'] as $option) {
                    $this->assertArrayHasKey('value', $option);
                    $this->assertArrayHasKey('label', $option);
                    $this->assertArrayHasKey('price', $option);
                    $this->assertArrayHasKey('is_default', $option);
                    $this->assertIsBool($option['is_default']);
                    $this->assertIsString($option['price']);
                }
            }
        }
    }
}
