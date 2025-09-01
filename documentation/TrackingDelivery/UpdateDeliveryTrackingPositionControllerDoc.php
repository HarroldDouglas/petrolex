<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/api/tracking/delivery/{orderId}/position",
 *     summary="Update delivery tracking position",
 *     description="Updates the geographical position of a delivery.",
 *     operationId="api.tracking.delivery.position.update",
 *     tags={"Suivi de Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="orderId",
 *         in="path",
 *         required=true,
 *         description="The order ID of the delivery to update position",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Real-time position and tracking data",
 *
 *         @OA\JsonContent(
 *             required={"driver_lat", "driver_lng"},
 *
 *             @OA\Property(
 *                 property="driver_lat",
 *                 type="number",
 *                 format="float",
 *                 minimum=-90,
 *                 maximum=90,
 *                 description="Current latitude of the delivery person",
 *                 example=3.8495
 *             ),
 *             @OA\Property(
 *                 property="driver_lng",
 *                 type="number",
 *                 format="float",
 *                 minimum=-180,
 *                 maximum=180,
 *                 description="Current longitude of the delivery person",
 *                 example=11.5035
 *             ),
 *             @OA\Property(
 *                 property="current_speed",
 *                 type="number",
 *                 format="float",
 *                 minimum=0,
 *                 description="Current speed in km/h (optional)",
 *                 example=35.5
 *             ),
 *             @OA\Property(
 *                 property="progress_percentage",
 *                 type="number",
 *                 format="float",
 *                 minimum=0,
 *                 maximum=100,
 *                 description="Delivery progress percentage (optional)",
 *                 example=75.2
 *             ),
 *             @OA\Property(
 *                 property="distance_remaining",
 *                 type="number",
 *                 format="float",
 *                 minimum=0,
 *                 description="Remaining distance in kilometers (optional)",
 *                 example=2.8
 *             ),
 *             @OA\Property(
 *                 property="estimated_duration",
 *                 type="integer",
 *                 minimum=0,
 *                 description="Estimated duration in minutes (optional)",
 *                 example=8
 *             )
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
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(property="success", type="boolean", example=false),
 *                     @OA\Property(property="message", type="string", example="Erreur de validation."),
 *                     @OA\Property(
 *                         property="data",
 *                         type="object",
 *                         @OA\Property(
 *                             property="driver_lat",
 *                             type="array",
 *
 *                             @OA\Items(type="string", example="Le champ driver_lat est requis.")
 *                         ),
 *
 *                         @OA\Property(
 *                             property="driver_lng",
 *                             type="array",
 *
 *                             @OA\Items(type="string", example="Le champ driver_lng est requis.")
 *                         )
 *                     )
 *                 )
 *             }
 *         )
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
