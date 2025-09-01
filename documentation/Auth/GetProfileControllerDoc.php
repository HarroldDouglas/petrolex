<?php

namespace App\Http\Api\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/user",
 *     operationId="getProfile",
 *     summary="Récupérer le profil de l'utilisateur authentifié",
 *     description="Récupérer le profil de l'utilisateur authentifié",
 *     operationId="api.get-profile",
 *     tags={"Authentification"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="User profile",
 *
 *         @OA\JsonContent(ref="#/components/schemas/UserData")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated"
 *     )
 * )
 */
class GetProfileControllerDoc {}
