<?php

namespace Tests\Unit\Services;

use App\Constants\AuthConstants;
use App\DTOs\Auth\AuthDTO;
use App\DTOs\Auth\LoginCredentialsDTO;
use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\Contracts\AuthenticationServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\LegacyMockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @method LegacyMockInterface shouldReceive(string $name)
 */
class AuthenticationServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    private const MOCK_TOKEN = '1|plaintext_token_string';
    private const TEST_EMAIL = 'test@example.com';
    private const TEST_PHONE = '237699999999';
    private const TEST_PASSWORD = 'password';

    /** @var TokenRepositoryInterface|LegacyMockInterface */
    private TokenRepositoryInterface $tokenRepository;

    /** @var UserRepositoryInterface|LegacyMockInterface */
    private UserRepositoryInterface $userRepository;

    /** @var \App\Services\Auth\Contracts\OtpServiceInterface|LegacyMockInterface */
    private \App\Services\Auth\Contracts\OtpServiceInterface $otpService;

    private AuthenticationServiceInterface $authService;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenRepository = Mockery::mock(TokenRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->otpService = Mockery::mock(\App\Services\Auth\Contracts\OtpServiceInterface::class);
        $this->authService = new AuthenticationService($this->userRepository, $this->tokenRepository, $this->otpService);

        $this->user = User::factory()->create([
            'email' => self::TEST_EMAIL,
            'phone_number' => self::TEST_PHONE,
            'password' => Hash::make(self::TEST_PASSWORD),
        ]);
    }

    private function mockTokenCreation(): void
    {
        $newAccessToken = new NewAccessToken(new PersonalAccessToken, self::MOCK_TOKEN);
        $this->tokenRepository
            ->shouldReceive('createToken')
            ->once()
            ->with($this->user, AuthConstants::API_TOKEN_NAME)
            ->andReturn($newAccessToken->plainTextToken);
    }

    public static function loginCredentialsProvider(): array
    {
        return [
            'email authentication' => [self::TEST_EMAIL, 'findByEmail'],
            'phone authentication' => [self::TEST_PHONE, 'findByPhone'],
        ];
    }

    #[Test]
    #[DataProvider('loginCredentialsProvider')]
    public function authentication_successful_with_valid_credentials(string $login, string $findMethod): void
    {
        $credentials = new LoginCredentialsDTO($login, self::TEST_PASSWORD);

        $this->userRepository
            ->shouldReceive($findMethod)
            ->once()
            ->with($login)
            ->andReturn($this->user);

        $this->mockTokenCreation();

        $result = $this->authService->authenticate($credentials);

        $this->assertInstanceOf(AuthDTO::class, $result);
        $this->assertEquals(self::MOCK_TOKEN, $result->token->accessToken);
        $this->assertEquals(AuthConstants::TOKEN_TYPE, $result->token->tokenType);
        $this->assertEquals($this->user->id, $result->user->id);
    }

    #[Test]
    public function authentication_fails_with_invalid_credentials(): void
    {
        $credentials = new LoginCredentialsDTO(self::TEST_EMAIL, 'wrong_password');

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->once()
            ->with(self::TEST_EMAIL)
            ->andReturn($this->user);

        $this->expectException(AuthenticationException::class);
        $this->authService->authenticate($credentials);
    }

    #[Test]
    public function get_authenticated_user_returns_current_user(): void
    {
        $this->actingAs($this->user);

        $result = $this->authService->getAuthenticatedUser();

        $this->assertEquals($this->user->id, $result->id);
    }

    #[Test]
    public function revoke_current_token_succeeds(): void
    {
        $tokenId = '1';

        $this->tokenRepository
            ->shouldReceive('revokeToken')
            ->once()
            ->with($tokenId, $this->user);

        $this->authService->revokeCurrentToken($tokenId, $this->user);

        $this->tokenRepository->shouldHaveReceived('revokeToken')->once();
    }

    #[Test]
    public function revoke_all_tokens_succeeds(): void
    {
        $this->tokenRepository
            ->shouldReceive('revokeAllTokens')
            ->once()
            ->with($this->user);

        $this->authService->revokeAllTokens($this->user);

        $this->tokenRepository->shouldHaveReceived('revokeAllTokens')->once();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
