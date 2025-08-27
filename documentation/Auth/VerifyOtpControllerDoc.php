<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/verify-otp",
 *     summary="Vérifier le code OTP",
 *     description="Vérifie le code OTP envoyé à l'utilisateur, marque son compte comme vérifié et retourne un token de réinitialisation de mot de passe.",
 *     operationId="api.verify-otp",
 *     tags={"Authentification"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={
 *                 "identifier",
 *                 "otp"
 *             },
 *
 *             @OA\Property(property="identifier", type="string", example="jean.dupont@example.com"),
 *             @OA\Property(property="otp", type="string", example="123456")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OTP vérifié avec succès.",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="_metadata", type="object",
 *                 properties={
 *                     @OA\Property(property="success", type="boolean", example=true),
 *                     @OA\Property(property="message", type="string", example="OTP verified successfully.")
 *                 }
 *             ),
 *             @OA\Property(property="data", type="object",
 *                 properties={
 *                     @OA\Property(property="identifier", type="string", example="jean.dupont@example.com"),
 *                     @OA\Property(property="reset_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkIjoxLCJlbWFpbCI6ImplYW4uZHVwb250QGV4YW1wbGUuY29tIiwiZXhwIjoxNzM0NTY3ODAwLCJpYXQiOjE3MzQ1Njc0MDAsImlzcyI6IlBldHJvbGV4In0.signature")
 *                 }
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="OTP ou identifiant invalide.",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class VerifyOtpControllerDoc {}
