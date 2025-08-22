<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/verify-otp",
 *     summary="Vérifier le code OTP",
 *     description="Vérifie le code OTP envoyé à l'utilisateur et marque son compte comme vérifié.",
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
 *                     @OA\Property(property="identifier", type="string", example="jean.dupont@example.com")
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
