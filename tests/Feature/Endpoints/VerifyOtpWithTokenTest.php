<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use App\Services\Auth\OtpService;
use App\Services\User\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class VerifyOtpWithTokenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function it_successfully_verifies_otp_and_returns_token(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false,
        ]);

        // Mock the OtpService to return a valid token
        $this->mock(OtpService::class, function ($mock) {
            $mock->shouldReceive('verifyOtpWithToken')
                ->once()
                ->with('test@example.com', '123456')
                ->andReturn('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.valid.token.here');
        });

        // Mock the UserService to return the user and mark email as verified
        $this->mock(UserService::class, function ($mock) use ($user) {
            $mock->shouldReceive('findUserByIdentifier')
                ->once()
                ->with('test@example.com')
                ->andReturn($user);

            $mock->shouldReceive('markEmailAsVerified')
                ->once()
                ->with($user);
        });

        $response = $this->postJson('/api/verify-otp', [
            'identifier' => 'test@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'OTP verified successfully.')
            ->assertJsonPath('data.identifier', 'test@example.com')
            ->assertJsonPath('data.reset_token', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.valid.token.here');
    }

    #[Test]
    public function it_returns_error_for_invalid_otp(): void
    {
        // Mock the OtpService to return null for invalid OTP
        $this->mock(OtpService::class, function ($mock) {
            $mock->shouldReceive('verifyOtpWithToken')
                ->once()
                ->with('test@example.com', '000000')
                ->andReturn(null);
        });

        $response = $this->postJson('/api/verify-otp', [
            'identifier' => 'test@example.com',
            'otp' => '000000',
        ]);

        $response->assertStatus(400)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Invalid OTP or identifier.');
    }

    #[Test]
    public function it_returns_error_for_missing_identifier(): void
    {
        $response = $this->postJson('/api/verify-otp', [
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['identifier']);
    }

    #[Test]
    public function it_returns_error_for_missing_otp(): void
    {
        $response = $this->postJson('/api/verify-otp', [
            'identifier' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);
    }

    #[Test]
    public function it_returns_error_for_invalid_otp_format(): void
    {
        $response = $this->postJson('/api/verify-otp', [
            'identifier' => 'test@example.com',
            'otp' => '12345', // Only 5 digits, should be 6
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);
    }

    #[Test]
    public function it_returns_error_for_non_numeric_otp(): void
    {
        $response = $this->postJson('/api/verify-otp', [
            'identifier' => 'test@example.com',
            'otp' => 'abcdef', // Non-numeric
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);
    }

    #[Test]
    public function it_marks_email_as_verified_when_user_exists(): void
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => false,
        ]);

        // Mock the OtpService to return a valid token
        $this->mock(OtpService::class, function ($mock) {
            $mock->shouldReceive('verifyOtpWithToken')
                ->once()
                ->with('test@example.com', '123456')
                ->andReturn('valid.token.here');
        });

        // Mock the UserService to return the user and mark email as verified
        $this->mock(UserService::class, function ($mock) use ($user) {
            $mock->shouldReceive('findUserByIdentifier')
                ->once()
                ->with('test@example.com')
                ->andReturn($user);

            $mock->shouldReceive('markEmailAsVerified')
                ->once()
                ->with($user);
        });

        $response = $this->postJson('/api/verify-otp', [
            'identifier' => 'test@example.com',
            'otp' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.reset_token', 'valid.token.here');
    }
}
