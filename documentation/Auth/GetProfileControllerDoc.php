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
 *         description="Profil utilisateur récupéré avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Profil récupéré avec succès.")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/UserData"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=false),
 *                 @OA\Property(property="message", type="string", example="Non authentifié.")
 *             )
 *         )
 *     )
 * )
 */
class GetProfileControllerDoc {}
