<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdatePasswordRequest",
 *     required={"old_password", "new_password", "new_password_confirmation"},
 *
 *     @OA\Property(property="old_password", type="string", format="password", example="password", description="Mot de passe actuel de l'utilisateur"),
 *     @OA\Property(property="new_password", type="string", format="password", example="new_strong_password", description="Nouveau mot de passe de l'utilisateur"),
 *     @OA\Property(property="new_password_confirmation", type="string", format="password", example="new_strong_password", description="Confirmation du nouveau mot de passe"),
 * )
 *
 * @OA\Schema(
 *     schema="UpdatePasswordResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 nullable=true,
 *                 example=null
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Mot de passe mis à jour avec succès."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Patch(
 *     path="/api/password",
 *     summary="Mettre à jour le mot de passe de l'utilisateur authentifié",
 *     description="Permet à l'utilisateur authentifié de mettre à jour son mot de passe.",
 *     operationId="api.password.update",
 *     tags={"Authentification"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Détails de l'ancien et du nouveau mot de passe.",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdatePasswordRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Mot de passe mis à jour avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdatePasswordResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation (ex: ancien mot de passe incorrect, nouveau mot de passe non conforme, ou trop faible)",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class UpdatePasswordControllerDoc {}
