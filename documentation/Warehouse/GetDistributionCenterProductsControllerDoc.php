<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DistributionCenterProductsResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/Product")
 *             ),
 *
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Produits récupérés avec succès"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/distribution-centers/{id}/products",
 *     summary="Récupérer les produits d'un centre de distribution",
 *     description="Récupère la liste de tous les produits disponibles dans un centre de distribution spécifique, avec leurs détails et quantités en stock. Les noms et descriptions des produits sont automatiquement traduits selon la langue de l'utilisateur authentifié (français par défaut, anglais disponible).",
 *     operationId="api.distribution-centers.products",
 *     tags={"Centres de Distribution"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID du centre de distribution",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste des produits récupérée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/DistributionCenterProductsResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Centre de distribution non trouvé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetDistributionCenterProductsControllerDoc {}
