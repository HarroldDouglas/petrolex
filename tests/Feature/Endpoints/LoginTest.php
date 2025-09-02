<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);
    }

    #[Test]
    public function it_can_login_with_email(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true);
    }

    #[Test]
    public function it_can_login_with_phone_and_country_id(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'country_id' => $this->user->country_id,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'phone_number',
                        'country',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true);
    }

    #[Test]
    public function it_can_login_with_phone_only_legacy(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);
    }

    #[Test]
    public function it_fails_login_with_invalid_credentials(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('_metadata.success', false);
    }
}
