<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/api/orders/{order}/cancel",
 *     summary="Cancel an order",
 *     description="Marks an order as cancelled.",
 *     operationId="api.orders.cancel",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID of the order to cancel",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Cancellation details",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CancelOrderRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Order cancelled successfully",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="_metadata", type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Order cancelled successfully.")
 *             ),
 *             @OA\Property(property="data", type="object", example={})
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Order not found",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *      @OA\Response(
 *         response=422,
 *         description="Validation error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
/**
 * @OA\Schema(
 *     schema="CancelOrderRequest",
 *     required={"cancelled_reason", "cancelled_by"},
 *
 *     @OA\Property(
 *         property="cancelled_reason",
 *         type="string",
 *         description="The reason for cancelling the order.",
 *         example="Customer changed their mind."
 *     ),
 *     @OA\Property(
 *         property="cancelled_by",
 *         type="integer",
 *         description="The ID of the user who cancelled the order.",
 *         example=1
 *     )
 * )
 */
class CancelOrderControllerDoc {}
