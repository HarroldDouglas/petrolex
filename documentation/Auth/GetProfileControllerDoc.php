<?php

namespace App\Http\Api\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/user",
 *     operationId="getProfile",
 *     summary="Get user profile",
 *     tags={"Authentification"},
 *     @OA\Response(
 *         response=200,
 *         description="User profile",
 *         @OA\JsonContent(ref="#/components/schemas/User")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
class GetProfileControllerDoc {}
