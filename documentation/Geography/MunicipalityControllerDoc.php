<?php
use OpenApi\Annotations as OA;

/**
 *
 * @OA\Schema(
 *     schema="MunicipalityData",
 *     description="Informations sur une municipalité",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Douala 1er"),
 *     @OA\Property(property="city_id", type="integer", example=1),
 *     @OA\Property(property="city_name", type="string", example="Douala"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-22T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-22T12:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="MunicipalitiesResponse",
 *     type="object",
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Liste des municipalités récupérée avec succès")
 *     ),
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/MunicipalityData")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="MunicipalityResponse",
 *     type="object",
 *     @OA\Property(
 *         property="_metadata",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Municipalité récupérée avec succès")
 *     ),
 *     @OA\Property(
 *         property="data",
 *         ref="#/components/schemas/MunicipalityData"
 *     )
 * )
 * @OA\Get(
 *     path="/api/geography/municipalities",
 *     summary="Lister les municipalités",
 *     description="Récupère la liste de toutes les municipalités.",
 *     operationId="api.geography.municipalities.index",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Liste des municipalités récupérée avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/MunicipalitiesResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/geography/municipalities/{municipality}",
 *     summary="Afficher une municipalité",
 *     description="Récupère les informations d'une municipalité par son identifiant.",
 *     operationId="api.geography.municipalities.show",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="municipality",
 *         in="path",
 *         required=true,
 *         description="ID de la municipalité",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Municipalité récupérée avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/MunicipalityResponse")
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
 *     )
 * )
 *
 * @OA\Post(
 *     path="/api/geography/municipalities",
 *     summary="Créer une municipalité",
 *     description="Crée une nouvelle municipalité.",
 *     operationId="api.geography.municipalities.store",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "city_id"},
 *             @OA\Property(property="name", type="string", example="Douala 1er"),
 *             @OA\Property(property="city_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Municipalité créée avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/MunicipalityResponse")
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 *
 * @OA\Put(
 *     path="/api/geography/municipalities/{municipality}",
 *     summary="Mettre à jour une municipalité",
 *     description="Met à jour les informations d'une municipalité.",
 *     operationId="api.geography.municipalities.update",
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
 *             @OA\Property(property="name", type="string", example="Douala 1er"),
 *             @OA\Property(property="city_id", type="integer", example=1)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Municipalité mise à jour avec succès",
 *         @OA\JsonContent(ref="#/components/schemas/MunicipalityResponse")
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
 *
 * @OA\Delete(
 *     path="/api/geography/municipalities/{municipality}",
 *     summary="Supprimer une municipalité",
 *     description="Supprime une municipalité par son identifiant.",
 *     operationId="api.geography.municipalities.destroy",
 *     tags={"Géographie"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="municipality",
 *         in="path",
 *         required=true,
 *         description="ID de la municipalité",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Municipalité supprimée avec succès"
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
 *     )
 * )
 */
class MunicipalityControllerDoc {}
