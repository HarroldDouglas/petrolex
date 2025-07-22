<?php

namespace App\Http\Api\Controllers\Auth;

use App\Enums\LoginChannel;
use App\Http\Api\Responses\OtpResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Services\Auth\OtpService;
use App\Services\User\UserService;

class VerifyOtpController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected UserService $userService
    ) {}

    public function __invoke(OtpVerificationRequest $request): OtpResponse
    {
        $identifier = $request->input('identifier');
        $otp = $request->input('otp');

        if (! $this->otpService->verifyOtp($identifier, $otp)) {
            return OtpResponse::error('Invalid OTP or identifier.', null, 400);
        }

        // Mark user as verified
        $user = $this->userService->findUserByIdentifier($identifier);

        if ($user) {
            if ($this->otpService->determineChannel($identifier) === LoginChannel::EMAIL()) {
                $user->markEmailAsVerified();
            } else {
                $user->markPhoneAsVerified();
            }
        }

        return OtpResponse::otpVerified($identifier);
    }
}
