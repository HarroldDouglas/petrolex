<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DeliveryTrackingData",
 *     title="Delivery Tracking Data",
 *     description="Schema for a delivery tracking record",
 *     @OA\Property(property="id", type="integer", format="int64", description="The ID of the delivery tracking record"),
 *     @OA\Property(property="order_id", type="integer", format="int64", description="The ID of the order being tracked"),
 *     @OA\Property(property="status", type="string", description="The current status of the delivery"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The date and time the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The date and time the record was last updated")
 * )
 */
class DeliveryTrackingData {}
