<?php

namespace App\Http\Api\Responses;

class OtpResponse extends ApiResponse
{
    public static function otpSent(string $identifier, ?string $token = null): self
    {
        $data = [
            'identifier' => $identifier,
        ];

        if ($token) {
            $data['reset_token'] = $token;
        }

        return new self(
            $data,
            'OTP sent successfully for verification.'
        );
    }

    public static function otpVerified(string $identifier, ?string $token = null): self
    {
        $data = [
            'identifier' => $identifier,
        ];

        if ($token) {
            $data['reset_token'] = $token;
        }

        return new self(
            $data,
            'OTP verified successfully.'
        );
    }

    public static function error(?string $message = null, mixed $data = null, int $statusCode = 400): self
    {
        return new self($data, $message, false, $statusCode);
    }
}
