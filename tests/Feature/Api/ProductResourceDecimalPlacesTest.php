<?php

namespace Tests\Feature\Api;

use App\Enums\Currency;
use App\Http\Api\Resources\ProductResource;
use App\Models\Geography\Country;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductResourceDecimalPlacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_resource_uses_user_country_decimal_places()
    {
        // Create countries with different currencies
        $countryXAF = Country::factory()->create([
            'code' => 'CM',
            'name' => 'Cameroon',
            'currency' => Currency::XAF(), // 0 decimal places
        ]);

        $countryUSD = Country::factory()->create([
            'code' => 'US',
            'name' => 'United States',
            'currency' => Currency::USD(), // 2 decimal places
        ]);

        // Create users with different countries
        $userCameroon = User::factory()->create(['country_id' => $countryXAF->id]);
        $userCameroon->load('country'); // Ensure country relationship is loaded
        $userUS = User::factory()->create(['country_id' => $countryUSD->id]);
        $userUS->load('country'); // Ensure country relationship is loaded

        // Create an accessory type with a known price for testing
        $accessoryType = \App\Models\AccessoryType::factory()->create([
            'price' => 1000.50, // Price with decimals
        ]);

        // Create a product category for testing
        $productCategory = ProductCategory::factory()->create([
            'product_type' => \App\Enums\ProductType::ACCESSORY(),
            'product_type_id' => $accessoryType->id,
        ]);

        // Test with Cameroon user (0 decimal places)
        Sanctum::actingAs($userCameroon);
        request()->setUserResolver(function () use ($userCameroon) {
            return $userCameroon;
        });

        $resource = new ProductResource($productCategory);
        $arrayData = $resource->toArray(request());

        if (isset($arrayData['options'])) {
            foreach ($arrayData['options'] as $option) {
                // For XAF currency, prices should have 0 decimal places (rounded)
                $this->assertMatchesRegularExpression('/^\d+$/', $option['price'],
                    'XAF prices should have 0 decimal places');
                $this->assertEquals('1001', $option['price'], 'Price should be rounded to whole number');
            }
        }

        // Test with US user (2 decimal places)
        Sanctum::actingAs($userUS);
        request()->setUserResolver(function () use ($userUS) {
            return $userUS;
        });

        $resource = new ProductResource($productCategory);
        $arrayData = $resource->toArray(request());

        if (isset($arrayData['options'])) {
            foreach ($arrayData['options'] as $option) {
                // For USD currency, prices should have 2 decimal places
                $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $option['price'],
                    'USD prices should have 2 decimal places');
                $this->assertEquals('1000.50', $option['price'], 'Price should maintain 2 decimal places');
            }
        }
    }

    public function test_product_resource_falls_back_to_default_decimal_places_when_no_user_country()
    {
        // Create a user without country
        $user = User::factory()->create(['country_id' => null]);

        // Create a product category for testing
        $productCategory = ProductCategory::factory()->create();

        Sanctum::actingAs($user);
        $resource = new ProductResource($productCategory);
        $arrayData = $resource->toArray(request());

        // Should fall back to default decimal places (2)
        if (isset($arrayData['options'])) {
            foreach ($arrayData['options'] as $option) {
                $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $option['price'],
                    'Should fall back to default 2 decimal places when no user country');
            }
        }
    }
}
