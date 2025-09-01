<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private string $authToken;
    private string $oldPassword = 'password';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);
    }

    #[Test]
    public function it_can_update_authenticated_user_password(): void
    {
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);

        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
        ]);
        $this->authToken = $response->json('data.access_token');

        $this->actingAs($this->user);

        $newPassword = 'new_strong_password';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->authToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ]);

        $response->assertOk();
        $response->assertJsonPath('_metadata.success', true);

        $this->assertTrue(Hash::check($newPassword, $this->user->fresh()->password));
    }

    #[Test]
    public function it_cannot_update_password_with_incorrect_old_password(): void
    {
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);

        $this->actingAs($this->user);

        $newPassword = 'new_strong_password';

        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => 'wrong_password',
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['old_password']);
    }

    #[Test]
    public function it_cannot_update_password_with_invalid_new_password(): void
    {
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);

        $this->actingAs($this->user);

        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => '123',
            'new_password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['new_password']);

        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => 'new_strong_password',
            'new_password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['new_password']);
    }

    #[Test]
    public function unauthenticated_user_cannot_update_password(): void
    {
        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => 'new_strong_password',
            'new_password_confirmation' => 'new_strong_password',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function it_sends_a_notification_when_password_is_updated()
    {
        \Illuminate\Support\Facades\Notification::fake();

        $this->actingAs($this->user, 'sanctum')
            ->patchJson(route('api.password.update'), [
                'old_password' => $this->oldPassword,
                'new_password' => 'new_strong_password',
                'new_password_confirmation' => 'new_strong_password',
            ])
            ->assertStatus(200);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $this->user,
            \App\Notifications\PasswordUpdatedNotification::class
        );
    }
}
