<?php

namespace App\Http\Api\Controllers;

use App\Http\Api\Requests\Auth\LoginRequest;
use App\Http\Api\Resources\UserResource;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\Auth\LoginResponse;
use App\Http\Controllers\Controller;
use App\Services\Auth\AuthenticationService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @var AuthenticationService
     */
    protected $authService;

    /**
     * AuthController constructor.
     */
    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Authentifier un utilisateur et générer un token.
     *
     * @return \App\Http\Api\Responses\ApiResponse
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authService->attemptLogin($request->credentials());
            $token = $this->authService->createToken($user);

            return LoginResponse::fromUserAndToken($user, $token);

        } catch (AuthenticationException $e) {
            return ApiResponse::error($e->getMessage(), null, 401);
        }
    }

    /**
     * Déconnecter un utilisateur.
     *
     * @return \App\Http\Api\Responses\ApiResponse
     */
    public function logout(Request $request)
    {
        $this->authService->revokeCurrentToken($request->user());

        return ApiResponse::success(null, 'Déconnexion réussie');
    }

    /**
     * Récupérer les informations de l'utilisateur connecté.
     *
     * @return \App\Http\Api\Responses\ApiResponse
     */
    public function user(Request $request)
    {
        return ApiResponse::success(
            new UserResource($request->user()),
            'Informations utilisateur récupérées avec succès'
        );
    }
}
