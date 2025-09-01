<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/forgot-password",
 *     summary="Réinitialiser le mot de passe",
 *     description="Réinitialiser le mot de passe de l'utilisateur en utilisant le token de réinitialisation obtenu après vérification OTP. Aucune authentification requise.",
 *     operationId="api.forgot-password",
 *     tags={"Authentification"},
 *     security={},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"token", "password", "password_confirmation"},
 *
 *             @OA\Property(
 *                 property="token",
 *                 type="string",
 *                 example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
 *                 description="Token de réinitialisation obtenu après vérification OTP"
 *             ),
 *             @OA\Property(
 *                 property="password",
 *                 type="string",
 *                 format="password",
 *                 example="nouveauMotDePasse123",
 *                 description="Nouveau mot de passe (minimum 8 caractères)"
 *             ),
 *             @OA\Property(
 *                 property="password_confirmation",
 *                 type="string",
 *                 format="password",
 *                 example="nouveauMotDePasse123",
 *                 description="Confirmation du nouveau mot de passe"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Mot de passe réinitialisé avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Mot de passe réinitialisé avec succès.")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="email",
 *                     type="string",
 *                     example="user@example.com",
 *                     description="Email de l'utilisateur dont le mot de passe a été réinitialisé"
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Token invalide ou expiré",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Token de réinitialisation invalide ou expiré.")
 *             ),
 *             @OA\Property(property="data", type="null", example=null)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreurs de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Échec de la réinitialisation du mot de passe.")
 *             ),
 *             @OA\Property(property="data", type="null", example=null)
 *         )
 *     )
 * )
 */
class ForgotPasswordControllerDoc {}
