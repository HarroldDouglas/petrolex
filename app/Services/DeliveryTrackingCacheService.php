<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DeliveryTracking;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DeliveryTrackingCacheService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const POSITION_CACHE_TTL = 30; // 30 secondes pour les positions

    /**
     * Get tracking data from cache or database
     */
    public function getTrackingData(int $orderId): ?array
    {
        $cacheKey = "delivery_tracking:{$orderId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($orderId) {
            $tracking = DeliveryTracking::where('order_id', $orderId)
                ->with(['order.customer', 'order.deliveryPerson', 'order.deliveryAddress'])
                ->first();

            if (! $tracking) {
                return null;
            }

            return [
                'id' => $tracking->id,
                'order_id' => $tracking->order_id,
                'order_number' => $tracking->order->order_number,
                'status' => $tracking->status->value,
                'status_label' => $tracking->status->getLabel(),
                'driver_lat' => $tracking->driver_lat,
                'driver_lng' => $tracking->driver_lng,
                'estimated_duration' => $tracking->estimated_duration,
                'distance_remaining' => $tracking->distance_remaining,
                'current_speed' => $tracking->current_speed,
                'driver_name' => $tracking->order->deliveryPerson->full_name ?? null,
                'customer_name' => $tracking->order->customer->full_name ?? null,
                'destination_lat' => $tracking->order->destination_lat,
                'destination_lng' => $tracking->order->destination_lng,
                'started_at' => $tracking->started_at?->toISOString(),
                'delivered_at' => $tracking->delivered_at?->toISOString(),
                'updated_at' => $tracking->updated_at->toISOString(),
            ];
        });
    }

    /**
     * Cache position update with shorter TTL
     */
    public function cachePositionUpdate(int $orderId, array $positionData): void
    {
        $cacheKey = "delivery_position:{$orderId}";
        Cache::put($cacheKey, $positionData, self::POSITION_CACHE_TTL);

        // Also update the main tracking cache
        $this->invalidateTrackingCache($orderId);
    }

    /**
     * Invalidate tracking cache when data changes
     */
    public function invalidateTrackingCache(int $orderId): void
    {
        Cache::forget("delivery_tracking:{$orderId}");
        Cache::forget("delivery_position:{$orderId}");

        Log::debug("Cache invalidated for delivery tracking: {$orderId}");
    }

    /**
     * Get active deliveries from cache
     */
    public function getActiveDeliveries(): array
    {
        $cacheKey = 'active_deliveries';

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return DeliveryTracking::whereIn('status', ['started', 'in_progress'])
                ->with(['order.customer', 'order.deliveryPerson'])
                ->get()
                ->map(function ($tracking) {
                    return [
                        'order_id' => $tracking->order_id,
                        'order_number' => $tracking->order->order_number,
                        'driver_name' => $tracking->order->deliveryPerson->full_name ?? null,
                        'customer_name' => $tracking->order->customer->full_name ?? null,
                        'status' => $tracking->status->value,
                        'driver_lat' => $tracking->driver_lat,
                        'driver_lng' => $tracking->driver_lng,
                        'updated_at' => $tracking->updated_at->toISOString(),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Invalidate active deliveries cache
     */
    public function invalidateActiveDeliveriesCache(): void
    {
        Cache::forget('active_deliveries');
    }

    /**
     * Get cached metrics for dashboard
     */
    public function getDeliveryMetrics(): array
    {
        $cacheKey = 'delivery_metrics';

        return Cache::remember($cacheKey, 900, function () { // 15 minutes
            $today = now()->startOfDay();

            return [
                'total_deliveries_today' => DeliveryTracking::whereDate('created_at', $today)->count(),
                'completed_deliveries_today' => DeliveryTracking::whereDate('delivered_at', $today)->count(),
                'active_deliveries' => DeliveryTracking::whereIn('status', ['started', 'in_progress'])->count(),
                'average_delivery_time' => $this->calculateAverageDeliveryTime(),
                'last_updated' => now()->toISOString(),
            ];
        });
    }

    /**
     * Calculate average delivery time
     */
    private function calculateAverageDeliveryTime(): ?float
    {
        $completedDeliveries = DeliveryTracking::whereNotNull('delivered_at')
            ->whereNotNull('started_at')
            ->whereDate('delivered_at', '>=', now()->subDays(7))
            ->get();

        if ($completedDeliveries->isEmpty()) {
            return null;
        }

        $totalMinutes = $completedDeliveries->sum(function ($tracking) {
            return $tracking->started_at->diffInMinutes($tracking->delivered_at);
        });

        return round($totalMinutes / $completedDeliveries->count(), 1);
    }
}
