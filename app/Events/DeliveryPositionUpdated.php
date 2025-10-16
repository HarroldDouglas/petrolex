<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\DeliveryTracking;
use Exception;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Event fired when a delivery position is updated
 *
 * This event broadcasts real-time position updates for delivery tracking,
 * providing comprehensive delivery information to connected clients.
 */
class DeliveryPositionUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    private const EARTH_RADIUS_KM = 6371;
    private const EVENT_NAME = 'delivery-position-updated';
    private const EVENT_TYPE = 'position_update';

    public readonly DeliveryTracking $delivery;

    /**
     * Create a new delivery position updated event
     *
     * @param  DeliveryTracking  $delivery  The delivery tracking instance
     */
    public function __construct(DeliveryTracking $delivery)
    {
        $this->delivery = $delivery->load([
            'order.customer',
            'order.deliveryPerson',
            'order.deliveryAddress',
        ]);

        // 🔧 DEBUG : Log pour vérifier que l'événement est déclenché
        Log::info('DeliveryPositionUpdated event triggered', [
            'delivery_id' => $this->delivery->id,
            'order_id' => $this->delivery->order_id,
            'status' => $this->delivery->status->value,
        ]);
    }

    /**
     * Get the channels the event should broadcast on
     *
     * @return Channel[]
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('delivery-'.$this->delivery->order->order_number),
        ];
    }

    /**
     * Get the broadcaster connection to use
     */
    public function broadcastVia(): array
    {
        return ['reverb'];
    }

    /**
     * Get the broadcast event name
     */
    public function broadcastAs(): string
    {
        return self::EVENT_NAME;
    }

    /**
     * Get the data to broadcast with the event
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $routeData = $this->calculateRouteProgress();

        return [
            // Core delivery identifiers
            'id' => $this->delivery->id,
            'order_id' => $this->delivery->order_id,
            'order_number' => $this->delivery->order->order_number,

            // Current driver position
            'current_latitude' => $this->getDriverLatitude(),
            'current_longitude' => $this->getDriverLongitude(),
            'driver_position' => $this->getDriverPosition(),

            // Destination information
            'destination_latitude' => $this->getDestinationLatitude(),
            'destination_longitude' => $this->getDestinationLongitude(),
            'destination' => $this->getDestinationPosition(),
            'destination_address' => $this->getDestinationAddress(),

            // Delivery progress and timing
            'estimated_duration' => $this->delivery->estimated_duration,
            'estimated_time_remaining' => $this->delivery->estimated_duration,
            'eta' => $this->delivery->estimated_duration,
            'distance_remaining' => $this->getDistanceRemaining(),
            'current_speed' => $this->getCurrentSpeed(),
            'status' => $this->delivery->status->value,

            // Route progress calculation
            'total_distance' => $routeData['total_distance'],
            'progress_percentage' => $routeData['progress_percentage'],

            // Driver information
            'driver_name' => $this->getDriverName(),
            'driver_phone' => $this->getDriverPhone(),

            // Customer information
            'customer_name' => $this->getCustomerName(),
            'customer_phone' => $this->getCustomerPhone(),

            // Route and timing data
            'route_geometry' => $this->delivery->route_geometry,
            'started_at' => $this->delivery->started_at?->toISOString(),
            'updated_at' => $this->delivery->updated_at->toISOString(),

            // Event metadata
            '_event_type' => self::EVENT_TYPE,
            '_timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Calculate route progress data including total distance and percentage
     *
     * @return array{total_distance: float|null, progress_percentage: float|null}
     */
    private function calculateRouteProgress(): array
    {
        // 🔧 UTILISER DIRECTEMENT LES DONNÉES DU LIVREUR - PAS DE CALCUL !
        return [
            'total_distance' => null,
            'progress_percentage' => $this->delivery->progress_percentage !== null
                ? (float) $this->delivery->progress_percentage
                : null,
        ];
    }

    /**
     * Calculate the total route distance from route geometry
     *
     * @param  mixed  $routeGeometry  The route geometry data
     * @return float|null The total distance in kilometers, or null if calculation fails
     */
    private function calculateRouteDistance(mixed $routeGeometry): ?float
    {
        if (! $routeGeometry) {
            return null;
        }

        try {
            $geometry = $this->parseRouteGeometry($routeGeometry);

            if (! $this->isValidGeometry($geometry)) {
                return null;
            }

            return $this->calculateDistanceFromCoordinates($geometry['coordinates']);
        } catch (Exception $e) {
            Log::warning('Error calculating route distance', [
                'error' => $e->getMessage(),
                'delivery_id' => $this->delivery->id,
            ]);

            return null;
        }
    }

    /**
     * Parse route geometry from string or array format
     *
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    private function parseRouteGeometry(mixed $routeGeometry): array
    {
        if (is_string($routeGeometry)) {
            $decoded = json_decode($routeGeometry, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON in route geometry');
            }

            return $decoded;
        }

        if (! is_array($routeGeometry)) {
            throw new Exception('Route geometry must be string or array');
        }

        return $routeGeometry;
    }

    /**
     * Validate that geometry has required structure
     *
     * @param  array<string, mixed>  $geometry
     */
    private function isValidGeometry(array $geometry): bool
    {
        return isset($geometry['coordinates']) && is_array($geometry['coordinates']);
    }

    /**
     * Calculate total distance from coordinate array
     *
     * @param  array<int, array<int, float>>  $coordinates
     */
    private function calculateDistanceFromCoordinates(array $coordinates): float
    {
        $totalDistance = 0.0;
        $coordinateCount = count($coordinates);

        for ($i = 1; $i < $coordinateCount; $i++) {
            $totalDistance += $this->calculateHaversineDistance(
                $coordinates[$i - 1][1], // Previous latitude
                $coordinates[$i - 1][0], // Previous longitude
                $coordinates[$i][1],     // Current latitude
                $coordinates[$i][0]      // Current longitude
            );
        }

        return $totalDistance;
    }

    /**
     * Calculate distance between two GPS points using the Haversine formula
     *
     * @param  float  $lat1  First point latitude
     * @param  float  $lon1  First point longitude
     * @param  float  $lat2  Second point latitude
     * @param  float  $lon2  Second point longitude
     * @return float Distance in kilometers
     */
    private function calculateHaversineDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    /**
     * Get driver's current latitude as float
     */
    private function getDriverLatitude(): float
    {
        return (float) $this->delivery->driver_lat;
    }

    /**
     * Get driver's current longitude as float
     */
    private function getDriverLongitude(): float
    {
        return (float) $this->delivery->driver_lng;
    }

    /**
     * Get driver's current position as coordinate array
     *
     * @return array{lat: float, lng: float}
     */
    private function getDriverPosition(): array
    {
        return [
            'lat' => $this->getDriverLatitude(),
            'lng' => $this->getDriverLongitude(),
        ];
    }

    /**
     * Get destination latitude as float
     */
    private function getDestinationLatitude(): float
    {
        return (float) $this->delivery->order->destination_lat;
    }

    /**
     * Get destination longitude as float
     */
    private function getDestinationLongitude(): float
    {
        return (float) $this->delivery->order->destination_lng;
    }

    /**
     * Get destination position as coordinate array
     *
     * @return array{lat: float, lng: float}
     */
    private function getDestinationPosition(): array
    {
        return [
            'lat' => $this->getDestinationLatitude(),
            'lng' => $this->getDestinationLongitude(),
        ];
    }

    /**
     * Get formatted destination address
     */
    private function getDestinationAddress(): ?string
    {
        return $this->delivery->order->deliveryAddress->full_address ??
               $this->delivery->order->delivery_address ?? null;
    }

    /**
     * Get remaining distance as float
     */
    private function getDistanceRemaining(): float
    {
        return (float) $this->delivery->distance_remaining;
    }

    /**
     * Get current speed as float
     */
    private function getCurrentSpeed(): float
    {
        return (float) ($this->delivery->current_speed ?? 0);
    }

    /**
     * Get driver's full name
     */
    private function getDriverName(): ?string
    {
        return $this->delivery->order->deliveryPerson->full_name ??
               $this->delivery->order->deliveryPerson->name ?? null;
    }

    /**
     * Get driver's phone number
     */
    private function getDriverPhone(): ?string
    {
        return $this->delivery->order->deliveryPerson->phone_number ?? null;
    }

    /**
     * Get customer's full name
     */
    private function getCustomerName(): ?string
    {
        return $this->delivery->order->customer->full_name ??
               $this->delivery->order->customer->name ?? null;
    }

    /**
     * Get customer's phone number
     */
    private function getCustomerPhone(): ?string
    {
        return $this->delivery->order->customer->phone_number ?? null;
    }
}
