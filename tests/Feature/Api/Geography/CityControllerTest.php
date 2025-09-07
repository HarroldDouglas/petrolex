<?php

namespace Tests\Feature\Api\Geography;

use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cities_index_returns_neighborhoods_with_full_details()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test data manually to avoid factory issues
        $country = Country::create(['name' => 'Cameroon', 'code' => 'CM', 'is_active' => true]);
        $city = City::create([
            'name' => 'Douala',
            'country_id' => $country->id,
            'is_active' => true,
        ]);

        $municipality = Municipality::create([
            'name' => 'Douala 1er',
            'city_id' => $city->id,
            'is_active' => true,
        ]);

        $neighborhood = Neighborhood::create([
            'name' => 'Akwa',
            'municipality_id' => $municipality->id,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/geography/countries/{$country->id}/cities");

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'neighborhoods' => [
                            '*' => [
                                'id',
                                'name',
                                'municipality_id',
                                'municipality' => [
                                    'id',
                                    'name',
                                ],
                                'is_active',
                            ],
                        ],
                    ],
                ],
            ])
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Cities with neighborhoods retrieved successfully.',
                ],
                'data' => [
                    [
                        'id' => $city->id,
                        'name' => 'Douala',
                        'neighborhoods' => [
                            [
                                'id' => $neighborhood->id,
                                'name' => 'Akwa',
                                'municipality_id' => $municipality->id,
                                'municipality' => [
                                    'id' => $municipality->id,
                                    'name' => 'Douala 1er',
                                ],
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ]);
    }

    public function test_cities_index_returns_empty_neighborhoods_for_city_without_neighborhoods()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $country = Country::create(['name' => 'Cameroon', 'code' => 'CM', 'is_active' => true]);
        $city = City::create([
            'name' => 'Yaoundé',
            'country_id' => $country->id,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/geography/countries/{$country->id}/cities");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => $city->id,
                        'name' => 'Yaoundé',
                        'neighborhoods' => [],
                    ],
                ],
            ]);
    }

    public function test_cities_index_requires_authentication()
    {
        $country = Country::create(['name' => 'Test Country', 'code' => 'TC', 'is_active' => true]);

        $response = $this->getJson("/api/geography/countries/{$country->id}/cities");

        $response->assertStatus(401);
    }
}
