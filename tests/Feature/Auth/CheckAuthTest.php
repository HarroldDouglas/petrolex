<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'delivery@test.com',
            'password' => bcrypt('password123'),
        ]);
    }

    #[Test]
    public function test_authenticated_user_can_check_status(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/auth/check');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'authenticated',
                    'user_id',
                    'roles',
                ],
            ])
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'User is authenticated.',
                ],
                'data' => [
                    'authenticated' => true,
                    'user_id' => $this->user->id,
                ],
            ]);
    }

    #[Test]
    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/auth/check');

        $response->assertStatus(401);
    }

    #[Test]
    public function test_invalid_token_returns_401(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/auth/check');

        $response->assertStatus(401);
    }

    #[Test]
    public function test_check_returns_user_roles(): void
    {
        // Assign delivery_person role to user if your app uses roles
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/auth/check');

        $response->assertStatus(200)
            ->assertJsonPath('data.authenticated', true)
            ->assertJsonPath('data.user_id', $this->user->id)
            ->assertJsonStructure(['data' => ['roles']]);
    }
}
