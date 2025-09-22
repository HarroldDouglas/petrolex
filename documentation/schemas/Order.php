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
 *     @OA\Property(property="option", type="string", enum={"bottle_with_content", "content"}, nullable=true, example="bottle_with_content"),
 *     @OA\Property(property="option_label", type="string", example="Nouvelle bouteille"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-07-18T10:00:00Z")
 * )
 * All Order endpoints now use the unified OrderDetailsData schema for consistency.
 *
 * @see OrderDetailsData in GetOrderDetailsControllerDoc.php
 */
class OrderSchema {}
