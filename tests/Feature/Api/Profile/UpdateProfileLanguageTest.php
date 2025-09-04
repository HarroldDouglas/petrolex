<?php

namespace Tests\Feature\Api\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateProfileLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_profile_language_to_english()
    {
        $user = User::factory()->create([
            'language' => 'fr',
            'email' => 'test@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'language' => 'en',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone_number',
                    'language',
                ],
            ]);

        $user->refresh();
        $this->assertEquals('en', $user->language);
    }

    public function test_can_update_profile_language_to_french()
    {
        $user = User::factory()->create([
            'language' => 'en',
            'email' => 'test2@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'language' => 'fr',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('fr', $user->language);
    }

    public function test_profile_update_validates_language_enum()
    {
        $user = User::factory()->create([
            'language' => 'fr',
            'email' => 'test3@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'language' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
    }

    public function test_profile_update_accepts_null_language()
    {
        $user = User::factory()->create([
            'language' => 'en',
            'email' => 'test4@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/profile', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'phone_number' => $user->phone_number,
            'language' => null,
        ]);

        $response->assertStatus(200);

        // Language should remain unchanged when null is passed
        $user->refresh();
        $this->assertEquals('en', $user->language);
    }

    public function test_profile_update_without_language_field_keeps_existing()
    {
        $user = User::factory()->create([
            'language' => 'en',
            'email' => 'test5@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/profile', [
            'first_name' => 'Updated Name',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertEquals('en', $user->language);
        $this->assertEquals('Updated Name', $user->first_name);
    }

    public function test_unauthorized_user_cannot_update_profile()
    {
        $response = $this->patchJson('/api/profile', [
            'language' => 'en',
        ]);

        $response->assertStatus(401);
    }

    public function test_api_response_includes_language_in_user_data()
    {
        $user = User::factory()->create([
            'language' => 'fr',
            'email' => 'test6@example.com',
        ]);
        Sanctum::actingAs($user);

        $response = $this->patchJson('/api/profile', [
            'language' => 'en',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'language' => 'en',
                ],
            ]);
    }
}
