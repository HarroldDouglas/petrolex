<?php

namespace App\Http\Api\Controllers\Auth;

use App\Http\Api\Responses\OtpResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Services\Auth\OtpService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;

class ResendOtpController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected UserService $userService
    ) {}

    /**
     * Resend OTP code to user's email or phone.
     *
     * Route: POST /resend-otp
     * Name: api.resend-otp
     */
    public function __invoke(ResendOtpRequest $request): OtpResponse
    {
        $email = $request->input('email');

        $user = $this->userService->findUserByIdentifier($email);

        if (! $user) {
            return OtpResponse::error('Utilisateur non trouvé.', null, JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->otpService->sendOtp($user->email);

            return OtpResponse::otpSent($user->email, null, 200);
        } catch (\Exception $e) {
            return OtpResponse::error('Echec lors de l\'envoi de l\'OTP. Veuillez réessayer plus tard.', null, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
