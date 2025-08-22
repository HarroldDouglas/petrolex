<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="NeighborhoodData",
 *     description="Informations sur un quartier",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Bonapriso"),
 *     @OA\Property(property="city_id", type="integer", example=1),
 *     @OA\Property(property="city_name", type="string", example="Douala"),
 *     @OA\Property(property="latitude", type="number", format="float", example=4.0511),
 *     @OA\Property(property="longitude", type="number", format="float", example=9.7679)
 * )
 *
 * @OA\Schema(
 *     schema="NeighborhoodsResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/NeighborhoodData")
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Liste des quartiers récupérée avec succès"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/geography/cities/{cityId}/neighborhoods",
 *     summary="Lister les quartiers d'une ville",
 *     description="Récupère la liste des quartiers pour une ville donnée.",
 *     operationId="api.geography.cities.neighborhoods.index",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="cityId",
 *         in="path",
 *         required=true,
 *         description="ID de la ville",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Liste des quartiers récupérée avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/NeighborhoodsResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ville non trouvée",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class NeighborhoodControllerDoc {}