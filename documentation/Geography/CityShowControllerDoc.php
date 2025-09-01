<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/geography/cities/{cityId}",
 *     summary="Afficher les détails d'une ville",
 *     description="Récupère les informations détaillées d'une ville par son identifiant.",
 *     operationId="api.geography.cities.show",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="cityId",
 *         in="path",
 *         required=true,
 *         description="ID de la ville",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Détails de la ville récupérés avec succès",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(
 *                         property="data",
 *                         ref="#/components/schemas/CityData"
 *                     ),
 *                     @OA\Property(
 *                         property="message",
 *                         type="string",
 *                         example="Ville récupérée avec succès"
 *                     )
 *                 )
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Ville non trouvée",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class CityShowControllerDoc {}
