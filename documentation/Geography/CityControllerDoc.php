<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CityData",
 *     description="Informations sur une ville",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Douala"),
 *     @OA\Property(property="country_id", type="integer", example=1),
 *     @OA\Property(property="country_name", type="string", example="Cameroun"),
 *     @OA\Property(property="latitude", type="number", format="float", example=4.0511),
 *     @OA\Property(property="longitude", type="number", format="float", example=9.7679)
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
 *         @OA\Property(property="message", type="string", example="Liste des villes récupérée avec succès")
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
