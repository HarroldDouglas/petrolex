<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\DistributionCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DistributionCentersTest extends TestCase
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
    public function it_can_retrieve_a_list_of_distribution_centers(): void
    {
        DistributionCenter::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->getJson(route('api.distribution-centers.index'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'address',
                        'phone',
                        'email',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonCount(3, 'data');
    }
}
