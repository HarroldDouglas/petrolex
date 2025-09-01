<?php

namespace App\Http\Api\Controllers\Auth;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\OtpService;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;

class ForgotPasswordController extends Controller
{
    public function __construct(
        protected OtpService $otpService,
        protected UserService $userService
    ) {}

    /**
     * Reset user password using reset token.
     *
     * Route: POST /forgot-password
     * Name: api.forgot-password
     */
    public function __invoke(ForgotPasswordRequest $request): ApiResponse
    {
        $token = $request->input('token');
        $newPassword = $request->input('password');

        $tokenData = $this->otpService->verifyResetToken($token);

        if (! $tokenData) {
            return ApiResponse::error(
                message: 'Token de réinitialisation invalide ou expiré.',
                statusCode: JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $success = $this->userService->resetPassword($tokenData['email'], $newPassword);

        if (! $success) {
            return ApiResponse::error(
                message: 'Échec de la réinitialisation du mot de passe.',
                statusCode: JsonResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return ApiResponse::success(
            message: 'Mot de passe réinitialisé avec succès.',
            data: ['email' => $tokenData['email']]
        );
    }
}
