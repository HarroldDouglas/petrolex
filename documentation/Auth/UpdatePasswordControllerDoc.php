<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdatePasswordRequest",
 *     required={"old_password", "new_password", "new_password_confirmation"},
 *
 *     @OA\Property(property="old_password", type="string", format="password", example="password", description="User's current password"),
 *     @OA\Property(property="new_password", type="string", format="password", example="new_strong_password", description="User's new password"),
 *     @OA\Property(property="new_password_confirmation", type="string", format="password", example="new_strong_password", description="Confirmation of the new password"),
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
 *     summary="Update authenticated user's password",
 *     description="Allows the authenticated user to update their password.",
 *     operationId="api.password.update",
 *     tags={"Authentification"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Old and new password details.",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdatePasswordRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Password updated successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdatePasswordResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error (e.g., old password incorrect, new password mismatch, or too weak)",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class UpdatePasswordControllerDoc {}
