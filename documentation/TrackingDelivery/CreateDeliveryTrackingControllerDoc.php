<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/tracking/delivery",
 *     summary="Create a new delivery tracking record",
 *     description="Initializes a new tracking record for a delivery.",
 *     operationId="api.tracking.delivery.create",
 *     tags={"Suivi de Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Data for creating a new delivery tracking record",
 *         @OA\JsonContent(
 *             required={"order_id"},
 *             @OA\Property(property="order_id", type="integer", description="The ID of the order to be tracked", example=1)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Delivery tracking created successfully",
 *         @OA\JsonContent(
 *             allOf={
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *                     @OA\Property(property="data", ref="#/components/schemas/DeliveryTrackingData"),
 *                     @OA\Property(property="message", type="string", example="Suivi de livraison créé avec succès.")
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
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class CreateDeliveryTrackingControllerDoc {}
