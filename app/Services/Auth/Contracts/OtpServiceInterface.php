<?php

namespace App\Services\Auth\Contracts;

use App\Enums\LoginChannel;
use App\Models\User;

interface OtpServiceInterface
{
    /**
     * Generate and send an OTP to the user
     *
     * @param  string  $identifier  User's email or phone number
     * @return bool Success status of the operation
     *
     * @throws \App\Exceptions\UserNotFoundException
     * @throws \App\Exceptions\OtpDeliveryException
     */
    public function sendOtp(string $identifier): bool;

    /**
     * Verify if a provided OTP is valid for the user
     *
     * @param  string  $identifier  User's email or phone number
     * @param  string  $otp  OTP code to verify
     * @return bool True if the OTP is valid
     */
    public function verifyOtp(string $identifier, string $otp): bool;

    /**
     * Resend the OTP to the user
     *
     * @param  string  $identifier  User's email or phone number
     * @return bool Success status of the operation
     *
     * @throws \App\Exceptions\UserNotFoundException
     * @throws \App\Exceptions\OtpDeliveryException
     */
    public function resendOtp(string $identifier): bool;

    /**
     * Invalidate the OTP for a user
     *
     * @param  string  $identifier  User's email or phone number
     * @return bool Success status of the operation
     */
    public function invalidateOtp(string $identifier): bool;

    /**
     * Determine the appropriate delivery channel based on identifier format
     *
     * @param  string  $identifier  Email or phone number
     */
    public function determineChannel(string $identifier): LoginChannel;

    /**
     * Return a masked version of the identifier for display
     *
     * @param  string  $identifier  Email or phone number
     * @return string Masked identifier (e.g. j***@e***le.com or +123****890)
     */
    public function maskIdentifier(string $identifier): string;
}
