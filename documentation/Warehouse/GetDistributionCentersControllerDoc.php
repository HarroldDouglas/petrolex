<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DistributionCenterData",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Centre Principal"),
 *     @OA\Property(property="country", type="string", example="Cameroun"),
 *     @OA\Property(property="city", type="string", example="Douala"),
 *     @OA\Property(property="neighborhood", type="string", example="Bonanjo"),
 *     @OA\Property(property="address", type="string", example="123 Rue Principale, Douala"),
 *     @OA\Property(property="description", type="string", example="Centre de distribution principal avec toutes les commodités"),
 *     @OA\Property(property="latitude", type="string", example="4.05110000"),
 *     @OA\Property(property="longitude", type="string", example="9.76790000"),
 *     @OA\Property(property="phone", type="string", example="+237612345678"),
 *     @OA\Property(property="email", type="string", example="centre.principal@petrolex.cm"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="storage_capacity", type="string", nullable=true, example=null),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-04T14:47:11.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-04T14:47:11.000000Z")
 * )
 *
 * @OA\Schema(
 *     schema="DistributionCentersResponse",
 *     type="object",
 *
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Liste des centres de distribution récupérée avec succès")
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/DistributionCenterData")
 *     )
 * )
 */

/*
// Documentation supprimée - endpoint réservé aux tests internes uniquement
// @OA\Get(
//     path="/api/distribution-centers",
//     summary="Récupérer tous les centres de distribution",
//     description="Récupérer la liste de tous les centres de distribution.",
//     operationId="api.distribution-centers.index",
//     tags={"Centres de Distribution"},
//
//     security={{"bearerAuth":{}}},
//
//     @OA\Response(
//         response=200,
//         description="Liste récupérée avec succès",
//
//         @OA\JsonContent(ref="#/components/schemas/DistributionCentersResponse")
//     ),
//
//     @OA\Response(
//         response=401,
//         description="Non autorisé",
//
//         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
//     ),
//
//     @OA\Response(
//         response=500,
//         description="Erreur interne du serveur",
//
//         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
//     )
// )
*/
class GetDistributionCentersControllerDoc {}
