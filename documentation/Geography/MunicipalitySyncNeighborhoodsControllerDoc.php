<?php
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/geography/municipalities/{municipality}/neighborhoods",
 *     summary="Associer des quartiers à une municipalité",
 *     description="Synchronise la liste des quartiers associés à une municipalité donnée.",
 *     operationId="api.geography.municipalities.syncNeighborhoods",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="municipality",
 *         in="path",
 *         required=true,
 *         description="ID de la municipalité",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"neighborhood_ids"},
 *             @OA\Property(
 *                 property="neighborhood_ids",
 *                 type="array",
 *                 @OA\Items(type="integer", example=5),
 *                 description="Liste des IDs des quartiers à associer"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Quartiers synchronisés avec succès",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Quartiers synchronisés avec succès")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="neighborhoods",
 *                     type="array",
 *                     @OA\Items(ref="#/components/schemas/NeighborhoodData")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Municipalité non trouvée",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class MunicipalitySyncNeighborhoodsControllerDoc {}