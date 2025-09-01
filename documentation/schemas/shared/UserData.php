<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserData",
 *
 *     @OA\Property(property="id", type="integer", example=8),
 *     @OA\Property(property="first_name", type="string", example="Jean"),
 *     @OA\Property(property="last_name", type="string", example="Dupont"),
 *     @OA\Property(property="full_name", type="string", example="Jean Dupont"),
 *     @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
 *     @OA\Property(property="phone_number", type="string", example="+237612345678"),
 *     @OA\Property(property="address", type="string", example="123 Rue Principale, Douala"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string", enum={"delivery_person", "customer"}, example="delivery_person")),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time", example="2025-01-05T09:30:00Z", nullable=true),
 *     @OA\Property(property="phone_verified_at", type="string", format="date-time", example="2025-01-05T09:32:15Z", nullable=true),
 *     @OA\Property(property="last_login_at", type="string", format="date-time", example="2025-01-09T08:45:22Z", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-05T09:28:43Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-09T08:45:22Z"),
 * )
 */
class UserData {}
