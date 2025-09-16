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

class CityAlphabeticalOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_cities_are_returned_in_alphabetical_order()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test data
        $country = Country::create(['name' => 'Cameroon', 'code' => 'CM', 'is_active' => true]);

        // Create cities with names that should be sorted alphabetically
        $cityZ = City::create(['name' => 'Zomba', 'country_id' => $country->id, 'is_active' => true]);
        $cityA = City::create(['name' => 'Abuja', 'country_id' => $country->id, 'is_active' => true]);
        $cityM = City::create(['name' => 'Maroua', 'country_id' => $country->id, 'is_active' => true]);

        $response = $this->getJson("/api/geography/countries/{$country->id}/cities");

        $response->assertStatus(200);
        $cities = $response->json('data');

        // Verify cities are in alphabetical order
        $this->assertEquals('Abuja', $cities[0]['name']);
        $this->assertEquals('Maroua', $cities[1]['name']);
        $this->assertEquals('Zomba', $cities[2]['name']);
    }

    public function test_neighborhoods_are_returned_in_alphabetical_order()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test data
        $country = Country::create(['name' => 'Cameroon', 'code' => 'CM', 'is_active' => true]);
        $city = City::create(['name' => 'Douala', 'country_id' => $country->id, 'is_active' => true]);
        $municipality = Municipality::create(['name' => 'Douala 1er', 'city_id' => $city->id, 'is_active' => true]);

        // Create neighborhoods with names that should be sorted alphabetically
        Neighborhood::create(['name' => 'Zebra', 'municipality_id' => $municipality->id, 'is_active' => true]);
        Neighborhood::create(['name' => 'Akwa', 'municipality_id' => $municipality->id, 'is_active' => true]);
        Neighborhood::create(['name' => 'Makepe', 'municipality_id' => $municipality->id, 'is_active' => true]);

        $response = $this->getJson("/api/geography/countries/{$country->id}/cities");

        $response->assertStatus(200);
        $neighborhoods = $response->json('data.0.neighborhoods');

        // Verify neighborhoods are in alphabetical order
        $this->assertEquals('Akwa', $neighborhoods[0]['name']);
        $this->assertEquals('Makepe', $neighborhoods[1]['name']);
        $this->assertEquals('Zebra', $neighborhoods[2]['name']);
    }
}
