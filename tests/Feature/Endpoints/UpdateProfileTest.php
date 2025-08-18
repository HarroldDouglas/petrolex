<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    /** @test */
    public function it_can_update_authenticated_user_profile(): void
    {
        $newFirstName = 'UpdatedFirstName';
        $newLastName = 'UpdatedLastName';
        $newEmail = 'updated.email@example.com';
        $newPhoneNumber = '+237677112233';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $newEmail,
            'phone_number' => $newPhoneNumber,
        ]);

        $response->assertOk()
            ->assertJsonPath('_metadata.success', true);

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
            'first_name' => $newFirstName,
            'last_name' => $newLastName,
            'email' => $newEmail,
            'phone_number' => $newPhoneNumber,
        ]);
    }

    /** @test */
    public function it_cannot_update_profile_with_invalid_data(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    /** @test */
    public function it_cannot_update_profile_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_cannot_update_profile_with_duplicate_phone_number(): void
    {
        User::factory()->create(['phone_number' => '+237699000000']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'phone_number' => '+237699000000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone_number']);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->patchJson(route('api.profile.update'), [
            'first_name' => 'Test',
        ]);

        $response->assertStatus(401);
    }
}
