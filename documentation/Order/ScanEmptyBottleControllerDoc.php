<?php

namespace App\Documentation\Order;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ScanEmptyBottleRequest",
 *     required={"barcode", "order_item_id"},
 *
 *     @OA\Property(property="barcode", type="string", description="The barcode of the empty bottle being returned.", example="BOTTLE-12345"),
 *     @OA\Property(property="order_item_id", type="integer", description="The ID of the order item to associate the returned bottle with.", example=1)
 * )
 *
 * @OA\Schema(
 *     schema="ScanEmptyBottleResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 nullable=true,
 *                 example=null
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Empty bottle scanned successfully."
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Post(
 *     path="/api/orders/{order}/scan-empty-bottle",
 *     summary="Scan a returned empty bottle",
 *     description="Associates a returned empty bottle with an order item by scanning its barcode. If the bottle doesn't exist, it will be created.",
 *     operationId="api.orders.scan-empty-bottle",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID of the order.",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Barcode of the returned bottle and the associated order item ID.",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ScanEmptyBottleRequest")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Empty bottle scanned successfully",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ScanEmptyBottleResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request (e.g., validation error, or no available scan record to update)",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
 *         description="Order or OrderItem not found",
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
class ScanEmptyBottleControllerDoc {}
