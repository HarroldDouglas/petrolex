<?php

namespace App\Http\Api\Controllers\Auth;

use App\Contracts\Services\AuthenticationServiceInterface;
use App\Http\Api\Requests\Auth\RegisterRequest;
use App\Http\Api\Resources\UserResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class RegisterClientController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct(protected AuthenticationServiceInterface $authService) {}

    /*Register a new user account
    public function register(RegisterRequest $request): JsonResponse
    {
        $registerDTO = new RegisterCredentialsDTO(
            email: $request->input('email'),
            phone: $request->input('phone'),
            password: $request->input('password'),
            name: $request->input('name')
        );

        $user = $this->authService->register($registerDTO);
        $tokenDTO = $this->authService->createTokenForUser($user);

        return response()->json([
            'message' => 'Registration successful',
            'user' => new UserResource($user),
            'token' => $tokenDTO->accessToken,
            'token_type' => $tokenDTO->tokenType
        ], Response::HTTP_CREATED);
    }*/
}
