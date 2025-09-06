<?php

namespace Tests\Feature\Api\DistributionCenter;

use App\Models\DistributionCenter;
use App\Models\Geography\City;
use App\Models\Geography\Country;
use App\Models\Geography\Municipality;
use App\Models\Geography\Neighborhood;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetClosestDistributionCenterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_closest_distribution_center_with_coordinates()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test geographic data
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

        // Create distribution center
        $distributionCenter = DistributionCenter::create([
            'name' => 'Centre Akwa',
            'neighborhood_id' => $neighborhood->id,
            'address' => '123 Rue de la Paix',
            'latitude' => 4.0511,
            'longitude' => 9.7679,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/distribution-centers/closest?latitude=4.0511&longitude=9.7679');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    'id',
                    'name',
                    'address',
                ],
            ])
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Centre de distribution le plus proche trouvé.',
                ],
                'data' => [
                    'id' => $distributionCenter->id,
                    'name' => 'Centre Akwa',
                ],
            ]);
    }

    public function test_get_closest_distribution_center_with_neighborhood_id()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test geographic data
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

        // Create distribution center in the same municipality
        $distributionCenter = DistributionCenter::create([
            'name' => 'Centre Akwa',
            'neighborhood_id' => $neighborhood->id,
            'address' => '123 Rue de la Paix',
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/distribution-centers/closest?neighborhood_id={$neighborhood->id}");

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Centre de distribution le plus proche trouvé.',
                ],
                'data' => [
                    'id' => $distributionCenter->id,
                    'name' => 'Centre Akwa',
                ],
            ]);
    }

    public function test_get_closest_distribution_center_requires_coordinates_or_neighborhood_id()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/distribution-centers/closest');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'neighborhood_id']);
    }

    public function test_get_closest_distribution_center_requires_authentication()
    {
        $response = $this->getJson('/api/distribution-centers/closest?latitude=4.0511&longitude=9.7679');

        $response->assertStatus(401);
    }

    public function test_get_closest_distribution_center_returns_404_when_none_found()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/distribution-centers/closest?latitude=4.0511&longitude=9.7679');

        $response->assertStatus(404)
            ->assertJson([
                '_metadata' => [
                    'success' => false,
                    'message' => 'Aucun centre de distribution trouvé.',
                ],
            ]);
    }
}