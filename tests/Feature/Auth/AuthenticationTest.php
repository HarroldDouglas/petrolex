<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private const TEST_EMAIL = 'test@example.com';
    private const TEST_PHONE = '+1234567890';
    private const TEST_PASSWORD = 'password123';
    private const WRONG_PASSWORD = 'wrong_password';
    private const API_URL = [
        'login' => '/api/login',
        'logout' => '/api/logout',
        'profile' => '/api/user',
    ];

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => self::TEST_EMAIL,
            'phone_number' => self::TEST_PHONE,
            'password' => bcrypt(self::TEST_PASSWORD),
        ]);
    }

    #[Test]
    public function user_can_login_with_email(): void
    {
        $response = $this->attemptLogin(self::TEST_EMAIL, self::TEST_PASSWORD);

        $this->assertSuccessfulAuthentication($response);
    }

    #[Test]
    public function user_can_login_with_phone(): void
    {
        $response = $this->attemptLogin(self::TEST_PHONE, self::TEST_PASSWORD);

        $this->assertSuccessfulAuthentication($response);
    }

    #[Test]
    public function user_can_get_profile(): void
    {
        $token = $this->authenticateUser();

        $response = $this->getProfile($token);

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'phone_number',
                    'address',
                    'is_active',
                    'email_verified_at',
                    'phone_verified_at',
                    'last_login_at',
                    'roles',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    #[Test]
    public function user_can_logout_and_login_again(): void
    {
        $token = $this->authenticateUser();

        $this->logoutUser($token)->assertStatus(200);

        cache()->clear();
        $this->refreshApplication();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::API_URL['profile']);

        $response->assertStatus(401);
    }

    #[Test]
    public function invalid_credentials_are_rejected(): void
    {
        $response = $this->attemptLogin(self::TEST_EMAIL, self::WRONG_PASSWORD);

        $response->assertStatus(401)
            ->assertJson([
                '_metadata' => [
                    'success' => false,
                    'message' => 'Les identifiants fournits sont invalides, vérifiez bien votre email ou téléphone et votre mot de passe.',
                ],
            ]);
    }

    private function attemptLogin(string $login, string $password): \Illuminate\Testing\TestResponse
    {
        return $this->postJson(self::API_URL['login'], [
            'login' => $login,
            'password' => $password,
        ]);
    }

    private function authenticateUser(string $login = self::TEST_EMAIL): string
    {
        $response = $this->attemptLogin($login, self::TEST_PASSWORD);

        return json_decode($response->getContent())->data->access_token;
    }

    private function getProfile(string $token): \Illuminate\Testing\TestResponse
    {
        return $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::API_URL['profile']);
    }

    private function logoutUser(string $token): \Illuminate\Testing\TestResponse
    {
        return $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::API_URL['logout']);
    }

    private function assertSuccessfulAuthentication(\Illuminate\Testing\TestResponse $response): void
    {
        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'access_token',
                    'token_type',
                ],
            ]);

        $this->assertNotEmpty(
            json_decode($response->getContent())->data->access_token
        );
    }
}
