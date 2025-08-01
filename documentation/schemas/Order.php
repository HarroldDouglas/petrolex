<?php

namespace App\Documentation\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="OrderItemData",
 *     title="Order Item Data",
 *     description="Data of a single item within an order",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="product_category", ref="#/components/schemas/ProductCategoryData"),
 *     @OA\Property(property="quantity", type="integer", example=2),
 *     @OA\Property(property="unit_price", type="number", format="float", example=1500.00),
 *     @OA\Property(property="total_price", type="number", format="float", example=3000.00),
 *     @OA\Property(property="option", type="string", nullable=true, example="full"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-18T10:00:00Z")
 * )
 *
 * @OA\Schema(
 *     schema="OrderData",
 *     title="Order Data",
 *     description="Data of a single order",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="order_number", type="string", example="ORD-123456"),
 *     @OA\Property(property="delivery_type", type="string", example="home_delivery"),
 *     @OA\Property(property="payment_method", type="string", example="cash"),
 *     @OA\Property(property="total_amount", type="number", format="float", example=6000.00),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/OrderItemData")),
 *     @OA\Property(property="delivery_address", type="object",
 *         @OA\Property(property="id", type="integer", example=7),
 *         @OA\Property(property="name", type="string", example="place Guichard\n13682 Lelievre-la-Forêt, Cameroun")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-18T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-07-18T10:00:00Z"),
 *     @OA\Property(property="distribution_center", type="object",
 *         @OA\Property(property="latitude", type="number", format="float", example=48.8566),
 *         @OA\Property(property="longitude", type="number", format="float", example=2.3522)
 *     )
 * )
 */
class OrderSchema {}
