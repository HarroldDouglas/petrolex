<?php

namespace App\Documentation\Order;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrderDeliveredResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/OrderData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Order marked as delivered successfully."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Patch(
 *     path="/api/orders/{order}/deliver",
 *     summary="Mark an order as delivered",
 *     description="Changes the status of a specific order to DELIVERED. This action also triggers updates to associated bottle statuses and creates bottle movement entries.",
 *     operationId="api.orders.deliver",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID of the order to mark as delivered.",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Order successfully marked as delivered",
 *
 *         @OA\JsonContent(ref="#/components/schemas/OrderDeliveredResponse")
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
 *         description="Order not found",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Unprocessable Entity (e.g., order cannot be delivered in its current state)",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class DeliverOrderControllerDoc {}
