<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/delivery-persons/{deliveryPersonId}/orders",
 *     summary="Get all orders for a specific delivery person",
 *     description="Retrieves a paginated list of orders for a given delivery person, with optional filtering by status, order number, and delivery type.",
 *     operationId="api.delivery-persons.orders",
 *     tags={"Livreurs"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="deliveryPersonId",
 *         in="path",
 *         required=true,
 *         description="ID of the delivery person",
 *
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filter orders by status",
 *
 *         @OA\Schema(type="string", enum={"confirmed", "in_progress", "delivered", "cancelled", "pending"})
 *     ),
 *
 *     @OA\Parameter(
 *         name="order_number",
 *         in="query",
 *         required=false,
 *         description="Filter orders by order number (partial match)",
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         required=false,
 *         description="Filter orders by delivery type",
 *
 *         @OA\Schema(type="string", enum={"normal", "fast"})
 *     ),
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Number of orders per page",
 *
 *         @OA\Schema(type="integer", format="int32", minimum=1, default=10)
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
 *                         type="array",
 *
 *                         @OA\Items(ref="#/components/schemas/DeliveryPersonOrderData")
 *                     ),
 *
 *                     @OA\Property(property="message", type="string", example="Commandes du livreur récupérées avec succès")
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
 *         description="Delivery person not found",
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
class DeliveryPersonOrdersControllerDoc {}
