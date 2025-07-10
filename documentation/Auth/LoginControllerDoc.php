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
 *     @OA\Property(
 *         property="user",
 *         ref="#/components/schemas/CustomerData",
 *         description="Informations de l'utilisateur authentifié",
 *     )
 *  )
 *
 * @OA\Schema(
 *     schema="LoginResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/TokenData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Authentification réussie"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/login",
 *     summary="Authentification utilisateur",
 *     description="Authentifie un utilisateur et génère un token d'accès API",
 *     operationId="api.login",
 *     tags={"Authentication"},
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
