<?php

namespace App\Services\Auth;

use App\Enums\LoginChannel;
use App\Exceptions\UserNotFoundException;
use App\Mail\OtpMail;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\Contracts\OtpServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class OtpService implements OtpServiceInterface
{
    private const OTP_LENGTH = 6;
    private const OTP_TTL_MINUTES = 10;
    private const OTP_CACHE_PREFIX = 'otp_';
    private const SMS_MESSAGE_TEMPLATE = 'Votre code de vérification est %s. Ce code expirera dans 10 minutes.';
    private const RESET_TOKEN_TTL_MINUTES = 10;
    private const SECONDS_PER_MINUTE = 60;

    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function sendOtp(string $identifier): bool
    {
        $user = $this->userRepository->findByEmailOrPhone($identifier);

        if (! $user) {
            throw new UserNotFoundException($identifier);
        }

        $otp = $this->generateOtpCode();
        $channel = $this->determineChannel($identifier);
        $maskedIdentifier = $this->maskIdentifier($identifier);
        $cacheKey = $this->generateCacheKey($identifier);
        Cache::put($cacheKey, $otp, now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            if ($channel->equals(LoginChannel::EMAIL())) {
                $userLanguage = $user->language ?? 'fr';
                Mail::to($identifier)->send(new OtpMail($otp, $maskedIdentifier, $userLanguage));
            } else {
                $twilioService = app('twilio');
                $message = sprintf(self::SMS_MESSAGE_TEMPLATE, $otp);

                if (! $twilioService->sendSms($identifier, $message)) {
                    \Log::warning("Failed to deliver SMS to {$maskedIdentifier}");

                    return false;
                }
            }

            return true;
        } catch (\Exception $e) {
            // Log the error but don't block the registration process
            \Log::warning('OTP delivery failed: '.$e->getMessage());

            // Keep the OTP in cache so user can still verify manually if needed
            return false;
        }
    }

    /**
     * Generate a random OTP code
     */
    private function generateOtpCode(): string
    {
        return (string) random_int(
            pow(10, self::OTP_LENGTH - 1),
            pow(10, self::OTP_LENGTH) - 1
        );
    }

    /**
     * {@inheritdoc}
     */
    public function determineChannel(string $identifier): LoginChannel
    {
        return filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? LoginChannel::EMAIL()
            : LoginChannel::PHONE();
    }

    /**
     * {@inheritdoc}
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        $user = $this->verifyOtpAndGetUser($identifier, $otp);

        if ($user) {
            $this->userRepository->update($user, ['is_active' => true]);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function verifyOtpWithToken(string $identifier, string $otp): ?string
    {
        $user = $this->verifyOtpAndGetUser($identifier, $otp);

        if ($user) {
            return $this->generateSecureResetToken($user, $identifier);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function resendOtp(string $identifier): bool
    {
        $this->invalidateOtp($identifier);

        return $this->sendOtp($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateOtp(string $identifier): bool
    {
        $cacheKey = $this->generateCacheKey($identifier);

        return Cache::forget($cacheKey);
    }

    /**
     * Generate a cache key for storing OTPs
     *
     * @param  string  $identifier  Email or phone number
     */
    private function generateCacheKey(string $identifier): string
    {
        return self::OTP_CACHE_PREFIX.md5($identifier);
    }

    /**
     * Common OTP verification logic that returns the user if OTP is valid
     *
     * @param  string  $identifier  Email or phone number
     * @param  string  $otp  OTP code to verify
     * @return \App\Models\User|null User if OTP is valid, null otherwise
     */
    private function verifyOtpAndGetUser(string $identifier, string $otp): ?\App\Models\User
    {
        $cacheKey = $this->generateCacheKey($identifier);
        $storedOtp = Cache::get($cacheKey);

        if (! $storedOtp) {
            return null;
        }

        if ($storedOtp === $otp) {
            $this->invalidateOtp($identifier);
            $user = $this->userRepository->findByEmailOrPhone($identifier);

            if ($user) {
                $this->userRepository->update($user, [
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                return $user;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function maskIdentifier(string $identifier): string
    {
        $channel = $this->determineChannel($identifier);

        if ($channel->equals(LoginChannel::EMAIL())) {
            // Mask email: j***@e***le.com
            $parts = explode('@', $identifier);

            if (count($parts) !== 2) {
                return '******';
            }

            $name = $parts[0];
            $domain = $parts[1];

            $maskedName = substr($name, 0, 1).str_repeat('*', strlen($name) - 1);

            $domainParts = explode('.', $domain);
            $tld = array_pop($domainParts);
            $domainName = implode('.', $domainParts);

            $maskedDomain = substr($domainName, 0, 1).str_repeat('*', strlen($domainName) - 1).'.'.$tld;

            return $maskedName.'@'.$maskedDomain;
        } else {
            // Mask phone number: +123****890
            $length = strlen($identifier);

            if ($length <= 4) {
                return str_repeat('*', $length);
            }

            $start = substr($identifier, 0, 4);
            $end = substr($identifier, -3);
            $masked = str_repeat('*', $length - 7);

            return $start.$masked.$end;
        }
    }

    /**
     * Generate a secure reset token containing user ID, email, and expiration
     *
     * @param  \App\Models\User  $user  The user to generate token for
     * @param  string  $identifier  User's email or phone number
     * @return string JWT-like token
     */
    private function generateSecureResetToken(\App\Models\User $user, string $identifier): string
    {
        $payload = [
            'user_id' => $user->id,
            'email' => $user->email,
            'exp' => time() + (self::RESET_TOKEN_TTL_MINUTES * self::SECONDS_PER_MINUTE),
            'iat' => time(),
            'iss' => config('app.name'),
        ];

        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $payload = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac('sha256', "{$header}.{$payload}", config('app.key'), true));

        return "{$header}.{$payload}.{$signature}";
    }

    /**
     * {@inheritdoc}
     */
    public function verifyResetToken(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;

        try {
            $decodedPayload = json_decode(base64_decode($payload), true);

            if (! $decodedPayload || ! isset($decodedPayload['exp']) || ! isset($decodedPayload['user_id']) || ! isset($decodedPayload['email'])) {
                return null;
            }

            if ($decodedPayload['exp'] < time()) {
                return null;
            }

            $expectedSignature = base64_encode(hash_hmac('sha256', "{$header}.{$payload}", config('app.key'), true));

            if (! hash_equals($signature, $expectedSignature)) {
                return null;
            }

            return $decodedPayload;
        } catch (\Exception $e) {
            return null;
        }
    }
}
