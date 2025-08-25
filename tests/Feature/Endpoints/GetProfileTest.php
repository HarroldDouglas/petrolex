<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GetProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);
        $this->user->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_get_user_profile_successfully(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->authToken,
            'Accept' => 'application/json',
        ])->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);
    }
}
