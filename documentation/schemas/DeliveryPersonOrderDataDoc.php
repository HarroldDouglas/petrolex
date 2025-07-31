<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DeliveryPersonOrderData",
 *     title="Delivery Person Order Data",
 *     description="Detailed order information for a delivery person",
 *
 *     @OA\Property(property="id", type="integer", example=12),
 *     @OA\Property(property="order_number", type="string", example="ORD-802583"),
 *     @OA\Property(property="delivery_type", type="string", example="fast"),
 *     @OA\Property(property="subtotal", type="string", example="29000.00"),
 *     @OA\Property(property="delivery_fee", type="string", example="1000.00"),
 *     @OA\Property(property="total_amount", type="string", example="30000.00"),
 *     @OA\Property(property="order_date", type="string", format="date-time", example="2025-07-24T16:21:54.000000Z"),
 *     @OA\Property(property="delivery_date", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="status", type="string", example="in_progress"),
 *     @OA\Property(
 *         property="payment",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=12),
 *         @OA\Property(property="status", type="string", example="paid"),
 *         @OA\Property(property="date", type="string", format="date-time", example="2025-07-24T16:21:54.000000Z"),
 *         @OA\Property(property="reference", type="string", example="VISA-PUR @ 68845213-636474827797144"),
 *         @OA\Property(property="method", type="string", example="credit_card")
 *     ),
 *     @OA\Property(
 *         property="delivery_address",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=16),
 *         @OA\Property(property="name", type="string", example="8, place de Pons\n11583 Francois-sur-Normand"),
 *         @OA\Property(property="contact_name", type="string", example="Élodie Gaillard"),
 *         @OA\Property(property="email", type="string", example="matthieu.masse @example.org"),
 *         @OA\Property(property="city", type="string", nullable=true, example=null),
 *         @OA\Property(property="country", type="string", nullable=true, example=null),
 *         @OA\Property(property="neighborhood", type="string", nullable=true, example=null),
 *         @OA\Property(property="address_precision", type="string", example="Magni minus quasi minima eligendi commodi."),
 *         @OA\Property(property="latitude", type="string", example="-4.21324600"),
 *         @OA\Property(property="longitude", type="string", example="-4.04634900")
 *     )
 * )
 */
class DeliveryPersonOrderDataDoc {}
