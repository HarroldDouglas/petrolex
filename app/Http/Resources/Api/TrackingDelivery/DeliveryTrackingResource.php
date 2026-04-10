<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\TrackingDelivery;

use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Resources\Order\OrderDetailResource;
use App\Models\DeliveryTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin DeliveryTracking
 */
final class DeliveryTrackingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        logger()->debug('DeliveryTrackingResource toArray called', [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'status' => $this->status->value,
            'driver_lat' => $this->driver_lat,
            'driver_lng' => $this->driver_lng,
        ]);

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order' => $this->whenLoaded('order', fn () => new OrderDetailResource($this->order)),
            'customer' => $this->whenLoaded('order', fn () => $this->order->customer ? new CustomerResource($this->order->customer) : null
            ),
            'status' => $this->status->value,
            'driver_lat' => $this->driver_lat,
            'driver_lng' => $this->driver_lng,
            'destination_lat' => $this->whenLoaded('order', fn () => $this->order->deliveryAddress?->latitude),
            'destination_lng' => $this->whenLoaded('order', fn () => $this->order->deliveryAddress?->longitude),
            'destination_location_link' => $this->whenLoaded('order', fn () => $this->order->deliveryAddress?->location_link),
            'destination_address' => $this->whenLoaded('order', fn () => $this->order->deliveryAddress?->fullAddress()),
            'estimated_duration' => $this->estimated_duration,
            'distance_remaining' => $this->distance_remaining,
            'total_distance' => $this->total_distance,
            'current_speed' => $this->current_speed,
            'route_geometry' => $this->route_geometry,
            'started_at' => $this->started_at,
            'delivered_at' => $this->delivered_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'progress_percentage' => $this->progress_percentage,
        ];
    }
}
