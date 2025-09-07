<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="NeighborhoodWithMunicipalityData",
 *     description="Informations sur un quartier avec sa municipalité",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Akwa"),
 *     @OA\Property(property="municipality_id", type="integer", example=1),
 *     @OA\Property(property="municipality", type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="name", type="string", example="Douala 1er")
 *     ),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="CityData",
 *     description="Informations sur une ville avec ses quartiers",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Douala"),
 *     @OA\Property(property="neighborhoods", type="array", @OA\Items(ref="#/components/schemas/NeighborhoodWithMunicipalityData"))
 * )
 *
 * @OA\Schema(
 *     schema="CitiesResponse",
 *     type="object",
 *
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Cities with neighborhoods retrieved successfully.")
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/CityData")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/geography/countries/{country}/cities",
 *     summary="Lister les villes d'un pays",
 *     description="Récupère la liste des villes pour un pays donné.",
 *     operationId="api.geography.countries.cities.index",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="country",
 *         in="path",
 *         required=true,
 *         description="ID du pays",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste des villes récupérée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CitiesResponse")
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
 *         description="Pays non trouvé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class CityControllerDoc {}
