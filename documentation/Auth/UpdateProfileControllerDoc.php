<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UpdateProfileRequest",
 *
 *     @OA\Property(property="first_name", type="string", nullable=true, example="John", description="User's first name"),
 *     @OA\Property(property="last_name", type="string", nullable=true, example="Doe", description="User's last name"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="test@example.com", description="User's email address"),
 *     @OA\Property(property="phone_number", type="string", nullable=true, example="677123456", description="User's phone number"),
 *     @OA\Property(property="address", type="string", nullable=true, example="123 Main St", description="User's address"),
 *     @OA\Property(property="language", type="string", enum={"fr", "en"}, nullable=true, example="fr", description="User's preferred language (fr for French, en for English)"),
 * )
 *
 * @OA\Schema(
 *     schema="UpdateProfileResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *            @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/UserData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Profil mis à jour avec succès."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Patch(
 *     path="/api/profile",
 *     summary="Update authenticated user's profile",
 *     description="Allows the authenticated user (delivery person or customer) to update their profile information.",
 *     operationId="api.profile.update",
 *     tags={"Authentification"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Fields to update in the user's profile.",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UpdateProfileResponse")
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
 *         description="Validation error",
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
class UpdateProfileControllerDoc {}
