<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\DistributionCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClosestDistributionCenterTest extends TestCase
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

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password', // Default password from factory
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_find_the_closest_distribution_center(): void
    {
        // Test coordinates (from user's Insomnia output)
        $testLatitude = 4.050465;
        $testLongitude = 9.767000;

        // Create distribution centers with specific coordinates
        // Center 1: This should be the closest one to the test coordinates
        $center1 = DistributionCenter::factory()->create([
            'name' => 'Centre Principal',
            'latitude' => 4.05110000,
            'longitude' => 9.76790000,
        ]);

        // Center 2: Further away
        $center2 = DistributionCenter::factory()->create([
            'name' => 'Centre Secondaire',
            'latitude' => 4.00000000,
            'longitude' => 9.70000000,
        ]);

        // Center 3: Even further away
        $center3 = DistributionCenter::factory()->create([
            'name' => 'Centre Tertiaire',
            'latitude' => 3.80000000,
            'longitude' => 11.50000000,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest', [
            'latitude' => $testLatitude,
            'longitude' => $testLongitude,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    'id',
                    'name',
                    'country' => [
                        'id',
                        'name',
                        'code',
                    ],
                    'city' => [
                        'id',
                        'name',
                        'country' => [
                            'id',
                            'name',
                            'code',
                        ],
                    ],
                    'neighborhood' => [
                        'id',
                        'name',
                        'latitude',
                        'longitude',
                    ],
                    'address',
                    'description',
                    'latitude',
                    'longitude',
                    'phone',
                    'email',
                    'storage_capacity',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Centre de distribution le plus proche trouvé.')
            ->assertJsonPath('data.id', $center1->id)
            ->assertJsonPath('data.name', $center1->name)
            ->assertJsonPath('data.latitude', (string) $center1->latitude)
            ->assertJsonPath('data.longitude', (string) $center1->longitude);

        // Test with coordinates closer to center2
        $testLatitude = 4.0001;
        $testLongitude = 9.7001;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest', [
            'latitude' => $testLatitude,
            'longitude' => $testLongitude,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $center2->id);
    }

    /** @test */
    public function it_returns_404_if_no_distribution_center_found(): void
    {
        // No distribution centers created
        $testLatitude = 3.855;
        $testLongitude = 11.505;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest', [
            'latitude' => $testLatitude,
            'longitude' => $testLongitude,
        ]));

        $response->assertStatus(404)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Aucun centre de distribution trouvé.');
    }

    /** @test */
    public function it_returns_validation_errors_for_missing_coordinates(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest'));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    /** @test */
    public function it_returns_validation_errors_for_invalid_coordinates(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest', [
            'latitude' => 91.0,
            'longitude' => 181.0,
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }
}
