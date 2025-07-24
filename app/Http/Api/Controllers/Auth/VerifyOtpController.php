<?php

namespace App\Http\Api\Controllers\Auth;

use App\Http\Api\Responses\OtpResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Services\Auth\OtpService;
use App\Services\User\UserService;

/**
 * Controller for verifying OTP codes.
 */
class VerifyOtpController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected UserService $userService
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(OtpVerificationRequest $request): OtpResponse
    {
        $identifier = $request->input('identifier');
        $otp = $request->input('otp');

        if (! $this->otpService->verifyOtp($identifier, $otp)) {
            return OtpResponse::error('Invalid OTP or identifier.', null, 400);
        }

        $user = $this->userService->findUserByIdentifier($identifier);

        if ($user) {
            $this->userService->markEmailAsVerified($user);
        }

        return OtpResponse::otpVerified($identifier);
    }
}
