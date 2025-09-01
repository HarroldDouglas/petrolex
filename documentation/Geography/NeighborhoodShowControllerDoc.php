<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="NeighborhoodShowResponse",
 *     type="object",
 *
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         properties={
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Quartier récupéré avec succès")
 *         }
 *     ),
 *     @OA\Property(
 *         property="data",
 *         ref="#/components/schemas/NeighborhoodData"
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/geography/neighborhoods/{neighborhoodId}",
 *     summary="Afficher les détails d'un quartier",
 *     description="Récupère les informations détaillées d'un quartier par son identifiant.",
 *     operationId="api.geography.neighborhoods.show",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="neighborhoodId",
 *         in="path",
 *         required=true,
 *         description="ID du quartier",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Détails du quartier récupérés avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/NeighborhoodShowResponse")
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
 *         description="Quartier non trouvé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class NeighborhoodShowControllerDoc {}
