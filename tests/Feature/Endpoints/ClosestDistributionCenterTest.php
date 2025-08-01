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

    // Distribution centers coordinates in Yaounde
    private const DISTRIBUTION_CENTERS = [
        'Nkoabang' => ['latitude' => 3.868, 'longitude' => 11.538],
        'Mokolo' => ['latitude' => 3.8747, 'longitude' => 11.4997],
        'Bastos' => ['latitude' => 3.8667, 'longitude' => 11.5167],
        'Odza' => ['latitude' => 3.832, 'longitude' => 11.535],
        'Mimboman' => ['latitude' => 3.858, 'longitude' => 11.551],
    ];

    // Test coordinates in different neighborhoods of Yaounde
    private const TEST_COORDINATES = [
        'Nkoabang' => [
            'Lycee' => ['latitude' => 3.865, 'longitude' => 11.536],
            'Carrefour' => ['latitude' => 3.868, 'longitude' => 11.538],
            'Carrefour_Nsimalen' => ['latitude' => 3.871, 'longitude' => 11.541],
        ],
        'Mokolo' => [
            'Marche' => ['latitude' => 3.8747, 'longitude' => 11.4997],
            'Lycee' => ['latitude' => 3.872, 'longitude' => 11.503],
            'Montee_Jouvence' => ['latitude' => 3.875, 'longitude' => 11.501],
        ],
        'Bastos' => [
            'Centre' => ['latitude' => 3.8670, 'longitude' => 11.5170],
            'Hotel_Hilton' => ['latitude' => 3.8650, 'longitude' => 11.5180],
            'Ambassade_France' => ['latitude' => 3.8680, 'longitude' => 11.5160],
        ],
        'Odza' => [
            'Carrefour' => ['latitude' => 3.829, 'longitude' => 11.531],
            'Marche' => ['latitude' => 3.832, 'longitude' => 11.535],
            'Hopital' => ['latitude' => 3.825, 'longitude' => 11.529],
        ],
        'Mimboman' => [
            'Carrefour' => ['latitude' => 3.856, 'longitude' => 11.547],
            'Marche' => ['latitude' => 3.858, 'longitude' => 11.551],
            'Avenue_Mefou' => ['latitude' => 3.853, 'longitude' => 11.554],
        ],
    ];

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
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');

        // Seed distribution centers
        $this->seedDistributionCenters();
    }

    private function seedDistributionCenters(): void
    {
        foreach (self::DISTRIBUTION_CENTERS as $name => $coordinates) {
            DistributionCenter::factory()->create([
                'name' => "Centre {$name}",
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude'],
            ]);
        }
    }

    /**
     * @test
     *
     * @dataProvider closestDistributionCenterDataProvider
     */
    public function it_can_find_the_closest_distribution_center(
        string $neighborhood,
        string $location,
        float $latitude,
        float $longitude,
        string $expectedCenterName
    ): void {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest', [
            'latitude' => $latitude,
            'longitude' => $longitude,
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
            ->assertJsonPath('data.name', $expectedCenterName);

        // Additional assertion to ensure we get the expected center
        $responseData = $response->json('data');
        $this->assertEquals($expectedCenterName, $responseData['name']);

        // Log for debugging purposes
        $this->assertNotNull($responseData['id'], "Distribution center ID should not be null for {$neighborhood} - {$location}");
    }

    public static function closestDistributionCenterDataProvider(): array
    {
        return [
            // Nkoabang neighborhood tests
            ['Nkoabang', 'Lycee', 3.865, 11.536, 'Centre Nkoabang'],
            ['Nkoabang', 'Carrefour', 3.868, 11.538, 'Centre Nkoabang'],
            ['Nkoabang', 'Carrefour_Nsimalen', 3.871, 11.541, 'Centre Nkoabang'],

            // Mokolo neighborhood tests
            ['Mokolo', 'Marche', 3.8747, 11.4997, 'Centre Mokolo'],
            ['Mokolo', 'Lycee', 3.872, 11.503, 'Centre Mokolo'],
            ['Mokolo', 'Montee_Jouvence', 3.875, 11.501, 'Centre Mokolo'],

            // Bastos neighborhood tests
            ['Bastos', 'Centre', 3.8670, 11.5170, 'Centre Bastos'],
            ['Bastos', 'Hotel_Hilton', 3.8650, 11.5180, 'Centre Bastos'],
            ['Bastos', 'Ambassade_France', 3.8680, 11.5160, 'Centre Bastos'],

            // Odza neighborhood tests
            ['Odza', 'Carrefour', 3.829, 11.531, 'Centre Odza'],
            ['Odza', 'Marche', 3.832, 11.535, 'Centre Odza'],
            ['Odza', 'Hopital', 3.825, 11.529, 'Centre Odza'],

            // Mimboman neighborhood tests
            ['Mimboman', 'Carrefour', 3.856, 11.547, 'Centre Mimboman'],
            ['Mimboman', 'Marche', 3.858, 11.551, 'Centre Mimboman'],
            ['Mimboman', 'Avenue_Mefou', 3.853, 11.554, 'Centre Mimboman'],
        ];
    }

    /** @test */
    public function it_returns_404_if_no_distribution_center_found(): void
    {
        // Clear all distribution centers
        DistributionCenter::query()->delete();

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

    /** @test */
    public function it_requires_authentication(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest', [
            'latitude' => 3.868,
            'longitude' => 11.538,
        ]));

        $response->assertStatus(401);
    }

    /** @test */
    public function it_calculates_distance_correctly_between_multiple_centers(): void
    {
        // Test with coordinates that are equidistant between two centers
        // This should help verify the distance calculation algorithm
        $testLatitude = 3.8714; // Between Nkoabang and Bastos
        $testLongitude = 11.5274; // Between Nkoabang and Bastos

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.closest', [
            'latitude' => $testLatitude,
            'longitude' => $testLongitude,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        // The response should return one of the centers (the algorithm should pick the closest one)
        $centerName = $response->json('data.name');
        $this->assertContains($centerName, ['Centre Nkoabang', 'Centre Bastos', 'Centre Mimboman']);
    }
}
