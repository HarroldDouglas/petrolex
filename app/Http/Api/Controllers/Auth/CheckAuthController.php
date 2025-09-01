<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

final class CheckAuthController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/auth/check",
     *     summary="Check authentication status",
     *     description="Verifies if the current user session is valid and authenticated. This endpoint is ideal for session validation in mobile applications.",
     *     operationId="api.auth.check",
     *     tags={"Authentication"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="User is authenticated",
     *
     *         @OA\JsonContent(
     *             allOf={
     *
     *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
     *                 @OA\Schema(
     *
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="authenticated", type="boolean", example=true),
     *                         @OA\Property(property="user_id", type="integer", example=8),
     *                         @OA\Property(property="roles", type="array", @OA\Items(type="string", enum={"delivery_person", "customer"}, example="delivery_person"))
     *                     ),
     *                     @OA\Property(property="message", type="string", example="User is authenticated.")
     *                 )
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - Token missing or invalid",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * Check if the user is authenticated.
     *
     * Route: GET /api/auth/check
     * Name: api.auth.check
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            '_metadata' => [
                'success' => true,
                'message' => 'User is authenticated.',
            ],
            'data' => [
                'authenticated' => true,
                'user_id' => auth()->id(),
                'roles' => auth()->user()?->roles ?? [],
            ],
        ]);
    }
}