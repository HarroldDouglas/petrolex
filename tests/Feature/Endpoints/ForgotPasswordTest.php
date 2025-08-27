<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use App\Services\Auth\OtpService;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

final class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_successfully_resets_password_with_valid_token(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('oldpassword123')
        ]);

        // Mock the OtpService to return valid user data
        $this->mock(OtpService::class, function ($mock) use ($user) {
            $mock->shouldReceive('verifyResetToken')
                ->once()
                ->with('valid.token.here')
                ->andReturn([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'exp' => time() + 3600, // 1 hour from now
                    'iat' => time(),
                    'iss' => config('app.name')
                ]);
        });

        // Mock the UserService to return success
        $this->mock(UserService::class, function ($mock) {
            $mock->shouldReceive('resetPassword')
                ->once()
                ->with('test@example.com', 'newpassword123')
                ->andReturn(true);
        });

        $response = $this->postJson('/api/forgot-password', [
            'token' => 'valid.token.here',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Mot de passe réinitialisé avec succès.');
    }

    /** @test */
    public function it_returns_error_for_invalid_token(): void
    {
        // Mock the OtpService to return null for invalid token
        $this->mock(OtpService::class, function ($mock) {
            $mock->shouldReceive('verifyResetToken')
                ->once()
                ->with('invalid.token.here')
                ->andReturn(null);
        });

        $response = $this->postJson('/api/forgot-password', [
            'token' => 'invalid.token.here',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Token de réinitialisation invalide ou expiré.');
    }

    /** @test */
    public function it_returns_error_for_expired_token(): void
    {
        // Mock the OtpService to return expired token data
        $this->mock(OtpService::class, function ($mock) {
            $mock->shouldReceive('verifyResetToken')
                ->once()
                ->with('expired.token.here')
                ->andReturn([
                    'user_id' => 1,
                    'email' => 'test@example.com',
                    'exp' => time() - 3600, // 1 hour ago (expired)
                    'iat' => time() - 7200,
                    'iss' => config('app.name')
                ]);
        });

        $response = $this->postJson('/api/forgot-password', [
            'token' => 'expired.token.here',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Échec de la réinitialisation du mot de passe.');
    }

    /** @test */
    public function it_returns_error_for_missing_token(): void
    {
        $response = $this->postJson('/api/forgot-password', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }

    /** @test */
    public function it_returns_error_for_missing_password(): void
    {
        $response = $this->postJson('/api/forgot-password', [
            'token' => 'valid.token.here',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_returns_error_for_password_confirmation_mismatch(): void
    {
        $response = $this->postJson('/api/forgot-password', [
            'token' => 'valid.token.here',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_returns_error_for_short_password(): void
    {
        $response = $this->postJson('/api/forgot-password', [
            'token' => 'valid.token.here',
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_returns_error_when_password_reset_fails(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // Mock the OtpService to return valid user data
        $this->mock(OtpService::class, function ($mock) use ($user) {
            $mock->shouldReceive('verifyResetToken')
                ->once()
                ->with('valid.token.here')
                ->andReturn([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'exp' => time() + 3600,
                    'iat' => time(),
                    'iss' => config('app.name')
                ]);
        });

        // Mock the UserService to return failure
        $this->mock(UserService::class, function ($mock) {
            $mock->shouldReceive('resetPassword')
                ->once()
                ->with('test@example.com', 'newpassword123')
                ->andReturn(false);
        });

        $response = $this->postJson('/api/forgot-password', [
            'token' => 'valid.token.here',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Échec de la réinitialisation du mot de passe.');
    }
}
