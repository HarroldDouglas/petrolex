<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserResource",
 *     type="object",
 *     properties={
 *
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="first_name", type="string", example="John"),
 *         @OA\Property(property="last_name", type="string", example="Doe"),
 *         @OA\Property(property="full_name", type="string", example="John Doe"),
 *         @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *         @OA\Property(property="phone_number", type="string", example="+237677123456"),
 *         @OA\Property(property="address", type="string", nullable=true, example="123 Main St"),
 *         @OA\Property(property="language", type="string", enum={"fr", "en"}, example="fr", description="User's preferred language"),
 *         @OA\Property(property="current_balance", type="number", format="float", nullable=true, example=1500.00, description="Customer current balance (only for customers)"),
 *         @OA\Property(property="email_verified_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:30:00.000000Z"),
 *         @OA\Property(property="phone_verified_at", type="string", format="date-time", nullable=true, example="2024-01-15T10:30:00.000000Z"),
 *         @OA\Property(property="last_login_at", type="string", format="date-time", nullable=true, example="2024-12-01T08:45:00.000000Z"),
 *         @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"customer"}),
 *         @OA\Property(property="customer_id", type="integer", nullable=true, example=123, description="Customer ID if user is a customer"),
 *         @OA\Property(property="delivery_person_id", type="integer", nullable=true, example=456, description="Delivery person ID if user is a delivery person"),
 *         @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00.000000Z"),
 *         @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-01T08:45:00.000000Z"),
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="UpdateProfileRequest",
 *
 *     @OA\Property(property="first_name", type="string", nullable=true, example="John", description="User's first name"),
 *     @OA\Property(property="last_name", type="string", nullable=true, example="Doe", description="User's last name"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true, example="john.doe@example.com", description="User's email address"),
 *     @OA\Property(property="phone_number", type="string", nullable=true, example="+237677123456", description="User's phone number"),
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
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/UserResource"
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
