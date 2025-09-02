<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     required={"login", "password"},
 *
 *     @OA\Property(
 *         property="login",
 *         type="string",
 *         description="Email ou téléphone de l'utilisateur (+237690000000)",
 *         example="admin@petrolex.com"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         description="Mot de passe",
 *         example="password"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="TokenData",
 *
 *     @OA\Property(
 *         property="access_token",
 *         type="string",
 *         description="Token d'accès Bearer",
 *         example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
 *     ),
 *     @OA\Property(
 *         property="token_type",
 *         type="string",
 *         description="Type de token",
 *         example="bearer"
 *     ),
 *  )
 *
 * @OA\Schema(
 *     schema="AuthData",
 *     description="Authentication response data with token and user information",
 *
 *     @OA\Property(
 *         property="access_token",
 *         type="string",
 *         description="Bearer access token",
 *         example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
 *     ),
 *     @OA\Property(
 *         property="token_type",
 *         type="string",
 *         description="Token type",
 *         example="bearer"
 *     ),
 *     @OA\Property(
 *         property="expires_in",
 *         type="integer",
 *         description="Token expiration time in seconds",
 *         example=3600
 *     ),
 *     @OA\Property(
 *         property="user",
 *         oneOf={
 *
 *             @OA\Schema(ref="#/components/schemas/UserData"),
 *             @OA\Schema(ref="#/components/schemas/UserData")
 *         },
 *         description="Authenticated user information (UserData for regular users, UserData for customers)"
 *     )
 * )
 * @OA\Schema(
 *     schema="LoginResponse",
 *
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Authentification réussie")
 *     ),
 *     @OA\Property(
 *         property="data",
 *         ref="#/components/schemas/AuthData"
 *     )
 * )
 *
 * @OA\Post(
 *     path="/api/login",
 *     summary="Authentification utilisateur",
 *     description="Authentifie un utilisateur et génère un token d'accès API",
 *     operationId="api.login",
 *     tags={"Authentification"},
 *
 *     @OA\RequestBody(
 *         description="Informations d'authentification",
 *         required=true,
 *
 *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
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
 *         description="Identifiants invalides",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
class LoginControllerDoc {}
