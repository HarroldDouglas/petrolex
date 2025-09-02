<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CountryListResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/Country")
 *             ),
 *
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Liste des pays récupérée avec succès"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/geography/countries",
 *     summary="Récupérer la liste de tous les pays actifs",
 *     description="Retourne la liste de tous les pays actifs avec leur code téléphonique et devise",
 *     operationId="api.geography.countries.index",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste des pays récupérée avec succès",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CountryListResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetCountriesControllerDoc {}
