<?php

namespace Tests\Feature\Api\Geography;

use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MunicipalityValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_create_municipality_with_duplicate_name_in_same_city()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test data
        $country = Country::create(['name' => 'Cameroon', 'code' => 'CM', 'is_active' => true]);
        $city = City::create(['name' => 'Douala', 'country_id' => $country->id, 'is_active' => true]);

        // Create first municipality
        Municipality::create([
            'name' => 'Douala I',
            'city_id' => $city->id,
            'is_active' => true,
        ]);

        // Try to create another municipality with the same name in the same city
        $response = $this->postJson('/api/geography/municipalities', [
            'name' => 'Douala I',
            'city_id' => $city->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_can_create_municipality_with_same_name_in_different_city()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test data
        $country = Country::create(['name' => 'Cameroon', 'code' => 'CM', 'is_active' => true]);
        $city1 = City::create(['name' => 'Douala', 'country_id' => $country->id, 'is_active' => true]);
        $city2 = City::create(['name' => 'Yaoundé', 'country_id' => $country->id, 'is_active' => true]);

        // Create first municipality in Douala
        Municipality::create([
            'name' => 'Centre',
            'city_id' => $city1->id,
            'is_active' => true,
        ]);

        // Create another municipality with the same name in Yaoundé (should work)
        $response = $this->postJson('/api/geography/municipalities', [
            'name' => 'Centre',
            'city_id' => $city2->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Municipality created successfully.',
                ],
            ]);
    }
}
