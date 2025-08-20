<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/distribution-centers/closest",
 *     summary="Find the closest distribution center to a given latitude and longitude",
 *     description="Retrieves the closest distribution center based on provided geographical coordinates.",
 *     operationId="api.distribution-centers.closest",
 *     tags={"Centres de Distribution"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="latitude",
 *         in="query",
 *         required=true,
 *         description="Latitude of the location",
 *
 *         @OA\Schema(type="number", format="float")
 *     ),
 *
 *     @OA\Parameter(
 *         name="longitude",
 *         in="query",
 *         required=true,
 *         description="Longitude of the location",
 *
 *         @OA\Schema(type="number", format="float")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(property="data", ref="#/components/schemas/DistributionCenterData"),
 *                     @OA\Property(property="message", type="string", example="Centre de distribution le plus proche trouvé.")
 *                 )
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="No distribution center found",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(property="message", type="string", example="Aucun centre de distribution trouvé.")
 *                 )
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetClosestDistributionCenterControllerDoc {}
