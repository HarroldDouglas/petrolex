<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/api/tracking/delivery/{orderId}/complete",
 *     summary="Complete delivery tracking",
 *     description="Marks a delivery as completed with optional final position and notes. Accessible uniquement par le livreur assigné à cette commande.",
 *     operationId="api.tracking.delivery.complete",
 *     tags={"Livraison"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="orderId",
 *         in="path",
 *         required=true,
 *         description="The order ID of the delivery to complete",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=false,
 *         description="Optional completion data",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="final_latitude",
 *                 type="number",
 *                 format="float",
 *                 minimum=-90,
 *                 maximum=90,
 *                 description="Final delivery latitude",
 *                 example=3.8512
 *             ),
 *             @OA\Property(
 *                 property="final_longitude",
 *                 type="number",
 *                 format="float",
 *                 minimum=-180,
 *                 maximum=180,
 *                 description="Final delivery longitude",
 *                 example=11.5098
 *             ),
 *             @OA\Property(
 *                 property="notes",
 *                 type="string",
 *                 maxLength=500,
 *                 description="Delivery completion notes",
 *                 example="Livraison effectuée avec succès. Client satisfait, bouteilles livrées à domicile."
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Delivery completed successfully",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(property="data", ref="#/components/schemas/DeliveryTrackingData"),
 *                     @OA\Property(property="message", type="string", example="Delivery completed successfully.")
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
 *         response=400,
 *         description="Bad Request - Utilisateur non autorisé (seul le livreur assigné peut compléter la livraison)",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - Not authorized to complete this delivery",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Delivery tracking not found",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=409,
 *         description="Conflict - Delivery already completed",
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
class CompleteDeliveryTrackingControllerDoc {}
