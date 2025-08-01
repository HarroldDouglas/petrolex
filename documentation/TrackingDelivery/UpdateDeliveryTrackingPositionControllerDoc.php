<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/api/tracking/delivery/{orderNumber}/position",
 *     summary="Update delivery tracking position",
 *     description="Updates the geographical position of a delivery.",
 *     operationId="api.tracking.delivery.position.update",
 *     tags={"Suivi de Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="orderNumber",
 *         in="path",
 *         required=true,
 *         description="The order number of the delivery to update",
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Geographical coordinates of the delivery",
 *
 *         @OA\JsonContent(
 *             required={"latitude", "longitude"},
 *
 *             @OA\Property(property="latitude", type="number", format="float", description="The latitude of the delivery"),
 *             @OA\Property(property="longitude", type="number", format="float", description="The longitude of the delivery")
 *         )
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
 *                     @OA\Property(property="message", type="string", example="Delivery tracking position updated successfully.")
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
class UpdateDeliveryTrackingPositionControllerDoc {}
