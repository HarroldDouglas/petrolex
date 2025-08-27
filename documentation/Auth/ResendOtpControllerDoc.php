<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/resend-otp",
 *     summary="Renvoyer le code OTP",
 *     description="Renvoyer un nouveau code OTP à l'utilisateur via son email. Aucune authentification requise.",
 *     operationId="api.resend-otp",
 *     tags={"Authentification"},
 *     security={},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"email"},
 *
 *             @OA\Property(
 *                 property="email",
 *                 type="string",
 *                 format="email",
 *                 example="user@example.com",
 *                 description="Email de l'utilisateur"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="OTP renvoyé avec succès",
 *
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="OTP sent successfully for verification.")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="identifier",
 *                     type="string",
 *                     example="u***@e***le.com",
 *                     description="Identifiant masqué de l'utilisateur (email ou téléphone)"
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Erreur de validation ou utilisateur non trouvé",
 *
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Utilisateur non trouvé.")
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
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Echec lors de l'envoi de l'OTP. Veuillez réessayer plus tard.")
 *             ),
 *             @OA\Property(property="data", type="null", example=null)
 *         )
 *     )
 * )
 */
class ResendOtpControllerDoc {}
