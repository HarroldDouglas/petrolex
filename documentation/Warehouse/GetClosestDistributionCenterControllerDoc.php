<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/distribution-centers/closest",
 *     summary="Find the closest distribution center with its products",
 *     description="Retrieves the closest distribution center based on provided geographical coordinates or neighborhood ID, along with all available products at that center. When neighborhood_id is provided, prioritizes centers in the same municipality.",
 *     operationId="api.distribution-centers.closest",
 *     tags={"Centres de Distribution"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="latitude",
 *         in="query",
 *         required=false,
 *         description="Latitude of the location (required if neighborhood_id not provided)",
 *
 *         @OA\Schema(type="number", format="float", minimum=-90, maximum=90)
 *     ),
 *
 *     @OA\Parameter(
 *         name="longitude",
 *         in="query",
 *         required=false,
 *         description="Longitude of the location (required if neighborhood_id not provided)",
 *
 *         @OA\Schema(type="number", format="float", minimum=-180, maximum=180)
 *     ),
 *
 *     @OA\Parameter(
 *         name="neighborhood_id",
 *         in="query",
 *         required=false,
 *         description="ID of the neighborhood (required if latitude and longitude not provided)",
 *
 *         @OA\Schema(type="integer", example=1)
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
 *                     @OA\Property(
 *                         property="data",
 *                         type="object",
 *                         @OA\Property(property="distribution_center", ref="#/components/schemas/DistributionCenterData"),
 *                         @OA\Property(
 *                             property="products",
 *                             type="array",
 *
 *                             @OA\Items(ref="#/components/schemas/Product")
 *                         )
 *                     ),
 *
 *                     @OA\Property(property="message", type="string", example="Centre de distribution le plus proche trouvé avec ses produits")
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
