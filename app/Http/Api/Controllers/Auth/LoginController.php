<?php

namespace App\Http\Api\Controllers\Auth;

use App\DTOs\Auth\LoginCredentialsDTO;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\Auth\LoginResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\Contracts\AuthenticationServiceInterface;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function __construct(protected AuthenticationServiceInterface $authService) {}

    /**
     * Authenticate user and generate API token
     */
    public function __invoke(LoginRequest $request): ApiResponse
    {
        $credentials = new LoginCredentialsDTO(
            login: $request->input('login'),
            password: $request->input('password')
        );

        try {
            $tokenDTO = $this->authService->authenticate($credentials);

            return LoginResponse::withToken($tokenDTO);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                statusCode: JsonResponse::HTTP_UNAUTHORIZED
            );
        }
    }
}
