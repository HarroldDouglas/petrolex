<?php

namespace App\Services\Auth;

use App\Constants\AuthConstants;
use App\DTOs\Auth\AuthDTO;
use App\DTOs\Auth\LoginCredentialsDTO;
use App\DTOs\Auth\TokenDTO;
use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\Contracts\AuthenticationServiceInterface;
use App\Services\Auth\Contracts\OtpServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected TokenRepositoryInterface $tokenRepository,
        protected OtpServiceInterface $otpService
    ) {}

    /**
     * Attempt to authenticate a user with email or phone
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function authenticate(LoginCredentialsDTO $credentials): AuthDTO
    {
        $user = $this->findUser($credentials);

        if (! $user || ! Hash::check($credentials->password, $user->password)) {
            throw new AuthenticationException('Les identifiants fournis sont invalides, vérifiez bien votre email ou téléphone et votre mot de passe.');
        }

        // Verify user type matches the app type
        if ($credentials->appType === 'customer_app' && ! $user->isCustomer()) {
            throw new AuthenticationException('Ces identifiants ne correspondent pas à un compte client. Veuillez utiliser l\'application appropriée.');
        }

        if ($credentials->appType === 'delivery_app' && ! $user->isDeliveryPerson()) {
            throw new AuthenticationException('Ces identifiants ne correspondent pas à un compte livreur. Veuillez utiliser l\'application appropriée.');
        }

        if (! $user?->is_active) {
            $this->otpService->sendOtp($user->email ?? $user->phone_number);
            throw new AuthenticationException("Votre compte n'est pas encore activé, nous vous avons envoyé un code d'activation par mail.");
        }

        if (filter_var($credentials->login, FILTER_VALIDATE_EMAIL) && ! $user->email_verified_at) {
            throw new AuthenticationException("Votre adresse email n'est pas encore vérifiée. Veuillez vérifier votre boîte mail.");
        }

        $user->last_login_at = now();
        $user->save();

        $plainTextToken = $this->tokenRepository->createToken($user, AuthConstants::API_TOKEN_NAME);

        $token = new TokenDTO(
            accessToken: $plainTextToken,
            tokenType: AuthConstants::TOKEN_TYPE
        );

        return new AuthDTO(
            token: $token,
            user: $user
        );
    }

    private function findUser(LoginCredentialsDTO $credentials): ?User
    {
        if (filter_var($credentials->login, FILTER_VALIDATE_EMAIL)) {
            return $this->userRepository->findByEmail($credentials->login);
        }

        if ($credentials->countryCode) {
            $country = \App\Models\Geography\Country::where('code', strtoupper($credentials->countryCode))->first();
            if ($country) {
                return $this->userRepository->findByPhoneAndCountry($credentials->login, $country->id);
            }
        }

        return $this->userRepository->findByPhone($credentials->login);
    }

    /**
     * Revoke the user's current access token
     */
    public function revokeCurrentToken(string $tokenId, User $user): void
    {
        $this->tokenRepository->revokeToken($tokenId, $user);
    }

    /**
     * Revoke all tokens for a user
     */
    public function revokeAllTokens(User $user): void
    {
        $this->tokenRepository->revokeAllTokens($user);
    }

    /**
     * Get authenticated user
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }
}
