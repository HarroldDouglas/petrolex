<?php

namespace App\Http\Api\Controllers\Auth;

use App\DTOs\Auth\LoginCredentialsDTO;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\Auth\LoginResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Auth\Contracts\AuthenticationServiceInterface;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/login/customer",
 *     summary="Authentification client (Customer App)",
 *     description="Authentifie un utilisateur CLIENT uniquement et génère un token d'accès API. Ce endpoint est réservé à l'application mobile client (com.isogaz.customer).",
 *     operationId="api.login.customer",
 *     tags={"Authentification"},
 *
 *     @OA\RequestBody(
 *         description="Informations d'authentification",
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"login", "password"},
 *             @OA\Property(property="login", type="string", example="customer1@test.com", description="Email ou numéro de téléphone"),
 *             @OA\Property(property="password", type="string", format="password", example="password", description="Mot de passe"),
 *             @OA\Property(property="country_code", type="string", example="CM", description="Code ISO du pays (optionnel)")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Authentification réussie",
 *
 *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Identifiants invalides ou compte non client",
 *
 *         @OA\JsonContent(
 *             type="object",
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Ces identifiants ne correspondent pas à un compte client. Veuillez utiliser l'application appropriée.")
 *             ),
 *             @OA\Property(property="data", type="null", example=null)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
class LoginCustomerController extends Controller
{
    public function __construct(protected AuthenticationServiceInterface $authService) {}

    /**
     * Authenticate customer and generate API token.
     *
     * Route: POST /login/customer
     * Name: api.login.customer
     */
    public function __invoke(LoginRequest $request): ApiResponse
    {
        $credentials = new LoginCredentialsDTO(
            login: $request->input('login'),
            password: $request->input('password'),
            countryCode: $request->input('country_code'),
            appType: 'customer_app'
        );

        try {
            $tokenDTO = $this->authService->authenticate($credentials);

            return LoginResponse::withUserAndToken($tokenDTO);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                statusCode: JsonResponse::HTTP_UNAUTHORIZED
            );
        }
    }
}
