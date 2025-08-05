<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/tracking/delivery/{orderId}/start",
 *     summary="Start delivery tracking",
 *     description="Marks a delivery as started.",
 *     operationId="api.tracking.delivery.start",
 *     tags={"Suivi de Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="orderId",
 *         in="path",
 *         required=true,
 *         description="The order ID of the delivery to start tracking",
 *
 *         @OA\Schema(type="integer")
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
 *                     @OA\Property(property="data", ref="#/components/schemas/DeliveryTrackingData"),
 *                     @OA\Property(property="message", type="string", example="Delivery tracking started successfully.")
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
 *         response=404,
 *         description="Delivery not found",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
class StartDeliveryTrackingControllerDoc {}
