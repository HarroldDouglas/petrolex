<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/tracking/delivery/active",
 *     summary="Get all active deliveries",
 *     description="Retrieves a list of all deliveries that are currently active.",
 *     operationId="api.tracking.delivery.active",
 *     tags={"Suivi de Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             allOf={
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *                     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DeliveryTrackingData")),
 *                     @OA\Property(property="message", type="string", example="Active deliveries retrieved successfully.")
 *                 )
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetActiveDeliveriesControllerDoc {}
