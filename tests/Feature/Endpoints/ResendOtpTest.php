<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use App\Services\Auth\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ResendOtpTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->assignRole('admin');

        // Mock the OtpService
        $this->otpService = Mockery::mock(OtpService::class);
        $this->app->instance(OtpService::class, $this->otpService);
    }

    #[Test]
    public function it_can_resend_otp_successfully(): void
    {
        // Mock the OTP service to return success
        $this->otpService->shouldReceive('sendOtp')
            ->once()
            ->with('test@example.com')
            ->andReturn(true);

        $response = $this->postJson('/api/resend-otp', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('data.identifier', 'test@example.com');
    }

    #[Test]
    public function it_returns_422_for_invalid_email_format(): void
    {
        $response = $this->postJson('/api/resend-otp', [
            'email' => 'invalid-email',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_returns_422_when_email_is_missing(): void
    {
        $response = $this->postJson('/api/resend-otp', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_returns_422_when_user_not_found(): void
    {
        $response = $this->postJson('/api/resend-otp', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    #[Test]
    public function it_returns_500_when_otp_service_fails(): void
    {
        // Mock the OTP service to throw an exception
        $this->otpService->shouldReceive('sendOtp')
            ->once()
            ->with('test@example.com')
            ->andThrow(new \Exception('OTP service error'));

        $response = $this->postJson('/api/resend-otp', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(500)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Echec lors de l\'envoi de l\'OTP. Veuillez réessayer plus tard.');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
