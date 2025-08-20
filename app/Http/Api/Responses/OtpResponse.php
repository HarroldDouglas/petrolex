<?php

namespace App\Http\Api\Responses;

class OtpResponse extends ApiResponse
{
    public static function otpSent(string $identifier): self
    {
        return new self(
            [
                'identifier' => $identifier,
            ],
            'OTP sent successfully for verification.'
        );
    }

    public static function otpVerified(string $identifier): self
    {
        return new self(
            [
                'identifier' => $identifier,
            ],
            'OTP verified successfully.'
        );
    }

    public static function error(?string $message = null, mixed $data = null, int $statusCode = 400): self
    {
        return new self($data, $message, false, $statusCode);
    }
}
