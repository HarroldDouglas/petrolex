<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\TrackingDelivery;

use App\Models\DeliveryTracking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

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
            'order_number' => $this->whenLoaded('order', fn () => $this->order->order_number),
            'customer_name' => $this->whenLoaded('order', fn () => $this->order->customer->full_name ?? null),
            'driver_name' => $this->whenLoaded('order', fn () => $this->order->deliveryPerson->full_name ?? null),
            'driver_phone' => $this->whenLoaded('order', fn () => $this->order->deliveryPerson->phone_number ?? null),
            'status' => $this->status->value,
            'driver_lat' => $this->driver_lat,
            'driver_lng' => $this->driver_lng,
            'destination_lat' => $this->whenLoaded('order', fn () => $this->order->destination_lat),
            'destination_lng' => $this->whenLoaded('order', fn () => $this->order->destination_lng),
            'destination_address' => $this->whenLoaded('order', fn () => $this->order->deliveryAddress->full_address ?? null),
            'estimated_duration' => $this->estimated_duration,
            'distance_remaining' => $this->distance_remaining,
            'total_distance' => $this->total_distance,
            'current_speed' => $this->current_speed,
            'route_geometry' => $this->route_geometry,
            'started_at' => $this->started_at,
            'delivered_at' => $this->delivered_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'progress_percentage' => $this->calculateProgressPercentage(),
        ];
    }

    private function calculateProgressPercentage(): ?float
    {
        if (! $this->distance_remaining || ! $this->total_distance) {
            Log::info('Cannot calculate progress - missing data', [
                'distance_remaining' => $this->distance_remaining,
                'total_distance' => $this->total_distance,
            ]);

            return null;
        }

        $distanceTraveled = max(0, $this->total_distance - $this->distance_remaining);
        $progress = ($distanceTraveled / $this->total_distance) * 100;
        $finalProgress = max(0, min(100, $progress));

        Log::info('Progress calculated', [
            'total_distance' => $this->total_distance,
            'distance_remaining' => $this->distance_remaining,
            'distance_traveled' => $distanceTraveled,
            'progress_percentage' => $finalProgress,
        ]);

        return $finalProgress;
    }
}
