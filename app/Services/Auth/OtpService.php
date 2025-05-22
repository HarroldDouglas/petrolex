<?php

namespace App\Services\Auth;

use App\Enums\LoginChannel;
use App\Exceptions\Auth\OtpDeliveryException;
use App\Exceptions\SmsDeliveryException;
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
    private const SMS_MESSAGE_TEMPLATE = 'Your verification code is %s. This code will expire in 10 minutes.';

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
                Mail::to($identifier)->send(new OtpMail($otp, $maskedIdentifier));
            } else {
                $twilioService = app('twilio');
                $message = sprintf(self::SMS_MESSAGE_TEMPLATE, $otp);

                if (! $twilioService->sendSms($identifier, $message)) {
                    throw new SmsDeliveryException("Failed to deliver SMS to {$maskedIdentifier}");
                }
            }

            return true;
        } catch (\Exception $e) {
            Cache::forget($cacheKey);
            throw new OtpDeliveryException($e->getMessage());
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
            ? LoginChannel::email()
            : LoginChannel::phone();
    }

    /**
     * {@inheritdoc}
     */
    public function verifyOtp(string $identifier, string $otp): bool
    {
        $cacheKey = $this->generateCacheKey($identifier);
        $storedOtp = Cache::get($cacheKey);

        if (! $storedOtp) {
            return false;
        }

        if ($storedOtp === $otp) {
            $this->invalidateOtp($identifier);

            return true;
        }

        return false;
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
}
