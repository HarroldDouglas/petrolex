<?php

/**
 * @OA\Get(
 *     path="/api/customers/{customer}/orders",
 *     summary="Get orders for a specific customer",
 *     tags={"Customers"},
 *
 *     @OA\Parameter(
 *         name="customer",
 *         in="path",
 *         required=true,
 *         description="The ID of the customer",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Parameter(
 *         name="order_number",
 *         in="query",
 *         description="Filter by order number",
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filter by order status",
 *
 *         @OA\Schema(ref="#/components/schemas/OrderStatus")
 *     ),
 *
 *      @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         description="Filter by delivery type",
 *
 *         @OA\Schema(ref="#/components/schemas/DeliveryType")
 *     ),
 *
 *      @OA\Parameter(
 *         name="payment_method",
 *         in="query",
 *         description="Filter by payment method",
 *
 *         @OA\Schema(ref="#/components/schemas/PaymentMethod")
 *     ),
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Number of items per page",
 *
 *         @OA\Schema(type="integer", default=10)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/CustomerOrdersResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Customer not found"
 *     )
 * )
 */
