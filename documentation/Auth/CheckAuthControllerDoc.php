<?php

namespace App\Documentation\Auth;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CheckAuthData",
 *     title="CheckAuthData",
 *     description="Authentication check response data",
 *
 *     @OA\Property(property="authenticated", type="boolean", example=true, description="Whether the user is authenticated"),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=123, description="Current authenticated user ID"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="object"), example={{"id": 1, "name": "customer"}}, description="User roles")
 * )
 *
 * @OA\Schema(
 *     schema="CheckAuthResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/CheckAuthData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="User is authenticated."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/auth/check",
 *     summary="Check user authentication status",
 *     description="Verify if the current user is authenticated and return basic user information.",
 *     operationId="api.auth.check",
 *     tags={"Authentification"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Authentication status retrieved successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CheckAuthResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class CheckAuthControllerDoc {}
