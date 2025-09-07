<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdateProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private ?string $authToken = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed essential data
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\GeographicSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolePermissionSeeder']);

        // Create user with explicit password and ensure it's properly saved
        $this->adminUser = User::factory()->create([
            'email' => 'test.admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Assign admin role and refresh from database
        $this->adminUser->assignRole('admin');
        $this->adminUser->refresh();

        // Attempt authentication with retry for robustness
        $this->authenticateUser();
    }

    private function authenticateUser(): void
    {
        // Ensure the database transaction is committed and user exists
        $this->adminUser->refresh();

        // Verify user exists and has correct attributes
        $this->assertNotNull($this->adminUser->email);
        $this->assertNotNull($this->adminUser->email_verified_at);
        $this->assertTrue($this->adminUser->is_active);

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->email,
            'password' => 'password',
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->fail(
                'Authentication failed. Status: '.$response->getStatusCode().
                '. Response: '.$response->getContent().
                '. User ID: '.$this->adminUser->id.
                '. User email: '.$this->adminUser->email.
                '. User active: '.($this->adminUser->is_active ? 'true' : 'false').
                '. Email verified: '.($this->adminUser->email_verified_at ? 'true' : 'false')
            );
        }

        $this->authToken = $response->json('data.access_token');

        if (! $this->authToken) {
            $this->fail('Authentication succeeded but no token returned. Response: '.$response->getContent());
        }
    }

    #[Test]
    public function it_can_update_authenticated_user_profile(): void
    {
        $newFirstName = 'UpdatedFirstName';
        $newLastName = 'UpdatedLastName';
        $newEmail = 'updated.email@example.com';
        $newPhoneNumber = '+237677112233';

        $this->assertNotNull($this->authToken, 'Auth token should not be null');

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

    #[Test]
    public function it_cannot_update_profile_with_invalid_data(): void
    {
        $this->assertNotNull($this->authToken, 'Auth token should not be null');

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

    #[Test]
    public function it_cannot_update_profile_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->assertNotNull($this->authToken, 'Auth token should not be null');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'email' => 'existing@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_cannot_update_profile_with_duplicate_phone_number(): void
    {
        // Use the seeded country data
        $country = \App\Models\Geography\Country::where('code', 'CM')->first();

        // Create a user with specific phone number and country
        User::factory()->create([
            'phone_number' => '+237699000000',
            'country_id' => $country->id,
        ]);

        $this->assertNotNull($this->authToken, 'Auth token should not be null');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'phone_number' => '+237699000000',
            'country_code' => 'CM',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['phone_number']);
    }

    #[Test]
    public function unauthenticated_user_cannot_update_profile(): void
    {
        $response = $this->patchJson(route('api.profile.update'), [
            'first_name' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_can_update_user_language_to_english(): void
    {
        $this->assertNotNull($this->authToken, 'Auth token should not be null');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'language' => 'en',
        ]);

        $response->assertOk()
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.language', 'en');

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
            'language' => 'en',
        ]);
    }

    #[Test]
    public function it_can_update_user_language_to_french(): void
    {
        $this->adminUser->update(['language' => 'en']);

        $this->assertNotNull($this->authToken, 'Auth token should not be null');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'language' => 'fr',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.language', 'fr');

        $this->assertDatabaseHas('users', [
            'id' => $this->adminUser->id,
            'language' => 'fr',
        ]);
    }

    #[Test]
    public function it_validates_language_enum_values(): void
    {
        $this->assertNotNull($this->authToken, 'Auth token should not be null');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'language' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
    }

    #[Test]
    public function it_accepts_null_language_value(): void
    {
        $this->assertNotNull($this->authToken, 'Auth token should not be null');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.profile.update'), [
            'language' => null,
        ]);

        $response->assertOk();
    }
}
