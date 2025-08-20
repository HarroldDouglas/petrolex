<?php

namespace Tests\Feature\Services\Auth;

use App\Constants\AuthConstants;
use App\DTOs\Auth\LoginCredentialsDTO;
use App\Models\User;
use App\Services\Auth\AuthenticationService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthenticationService $authenticationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticationService = $this->app->make(AuthenticationService::class);
    }

    /**
     * @test
     */
    public function it_can_authenticate_a_user_with_email(): void
    {
        // Arrange
        $password = 'password';
        $user = User::factory()->create(['password' => Hash::make($password)]);
        $credentials = new LoginCredentialsDTO(
            login: $user->email,
            password: $password
        );

        // Act
        $authDTO = $this->authenticationService->authenticate($credentials);

        // Assert
        $this->assertEquals($user->id, $authDTO->user->id);
        $this->assertNotNull($authDTO->token->accessToken);
        $this->assertEquals(AuthConstants::TOKEN_TYPE, $authDTO->token->tokenType);
    }

    /**
     * @test
     */
    public function it_can_authenticate_a_user_with_phone(): void
    {
        // Arrange
        $password = 'password';
        $user = User::factory()->create(['password' => Hash::make($password)]);
        $credentials = new LoginCredentialsDTO(
            login: $user->phone_number,
            password: $password
        );

        // Act
        $authDTO = $this->authenticationService->authenticate($credentials);

        // Assert
        $this->assertEquals($user->id, $authDTO->user->id);
        $this->assertNotNull($authDTO->token->accessToken);
        $this->assertEquals(AuthConstants::TOKEN_TYPE, $authDTO->token->tokenType);
    }

    /**
     * @test
     */
    public function it_throws_authentication_exception_for_invalid_credentials(): void
    {
        // Arrange
        $user = User::factory()->create();
        $credentials = new LoginCredentialsDTO(
            login: $user->email,
            password: 'wrong-password'
        );

        // Assert
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Les identifiants fournits sont invalides, vérifiez bien votre email ou téléphone et votre mot de passe.');

        // Act
        $this->authenticationService->authenticate($credentials);
    }

    /**
     * @test
     */
    public function it_can_revoke_current_token(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = $user->createToken(AuthConstants::API_TOKEN_NAME);

        // Act
        $this->authenticationService->revokeCurrentToken($token->plainTextToken, $user);

        // Assert
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    /**
     * @test
     */
    public function it_can_revoke_all_tokens(): void
    {
        // Arrange
        $user = User::factory()->create();
        $user->createToken(AuthConstants::API_TOKEN_NAME);
        $user->createToken(AuthConstants::API_TOKEN_NAME);

        // Act
        $this->authenticationService->revokeAllTokens($user);

        // Assert
        $this->assertCount(0, $user->tokens);
    }

    /**
     * @test
     */
    public function it_can_get_authenticated_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $this->actingAs($user);

        // Act
        $authenticatedUser = $this->authenticationService->getAuthenticatedUser();

        // Assert
        $this->assertEquals($user->id, $authenticatedUser->id);
    }
}
