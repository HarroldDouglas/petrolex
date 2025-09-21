<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DeliveryTrackingData",
 *     title="Delivery Tracking Data",
 *     description="Schema for a delivery tracking record with real-time position data",
 *
 *     @OA\Property(property="id", type="integer", example=15, description="The ID of the delivery tracking record"),
 *     @OA\Property(property="order_id", type="integer", example=127, description="The ID of the order being tracked"),
 *     @OA\Property(property="delivery_person_id", type="integer", example=8, nullable=true, description="The ID of the delivery person"),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending", "started", "processing", "delivered", "cancelled"},
 *         example="processing",
 *         description="The current status of the delivery"
 *     ),
 *     @OA\Property(property="driver_lat", type="number", format="float", example=3.8480, description="Current latitude of the delivery person"),
 *     @OA\Property(property="driver_lng", type="number", format="float", example=11.5021, description="Current longitude of the delivery person"),
 *     @OA\Property(property="final_latitude", type="number", format="float", nullable=true, description="Final delivery latitude"),
 *     @OA\Property(property="final_longitude", type="number", format="float", nullable=true, description="Final delivery longitude"),
 *     @OA\Property(property="current_speed", type="number", format="float", example=28.5, description="Current speed in km/h"),
 *     @OA\Property(property="total_distance", type="number", format="float", example=12.8, description="Total distance in kilometers"),
 *     @OA\Property(property="distance_remaining", type="number", format="float", example=4.2, description="Remaining distance in kilometers"),
 *     @OA\Property(property="progress_percentage", type="number", format="float", minimum=0, maximum=100, example=67.2, description="Delivery progress percentage"),
 *     @OA\Property(property="estimated_duration", type="integer", example=18, description="Estimated duration in minutes"),
 *     @OA\Property(property="route_geometry", type="object", description="GeoJSON route geometry"),
 *     @OA\Property(property="delivery_notes", type="string", nullable=true, description="Delivery completion notes"),
 *     @OA\Property(property="started_at", type="string", format="date-time", example="2025-01-09T14:30:00Z", nullable=true, description="When the delivery was started"),
 *     @OA\Property(property="completed_at", type="string", format="date-time", nullable=true, description="When the delivery was completed"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-09T14:25:12Z", description="The date and time the record was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-09T14:47:35Z", description="The date and time the record was last updated"),
 *     @OA\Property(
 *         property="order",
 *         ref="#/components/schemas/OrderDetailsData",
 *         description="The order being tracked"
 *     ),
 *     @OA\Property(
 *         property="delivery_person",
 *         ref="#/components/schemas/UserData",
 *         description="The delivery person assigned to this delivery"
 *     ),
 *     @OA\Property(
 *         property="route",
 *         type="object",
 *         description="Calculated route information",
 *         @OA\Property(property="distance", type="number", format="float", example=12.8, description="Route distance in kilometers"),
 *         @OA\Property(property="duration", type="integer", example=25, description="Route duration in minutes"),
 *         @OA\Property(property="geometry", type="object", description="GeoJSON route geometry")
 *     )
 * )
 */
class DeliveryTrackingData {}
