<?php

namespace App\Jobs;

use App\Events\DeliveryPositionUpdated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Process Delivery Simulation Job
 * 
 * Handles the background processing of delivery simulation updates.
 * Sends real-time position updates via WebSocket.
 */
class ProcessDeliverySimulation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CACHE_PREFIX = 'delivery_simulation_';
    
    public int $orderId;
    public string $simulationId;
    public int $tries = 3;
    public int $timeout = 300; // 5 minutes max

    /**
     * Create a new job instance.
     */
    public function __construct(int $orderId, string $simulationId)
    {
        $this->orderId = $orderId;
        $this->simulationId = $simulationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = self::CACHE_PREFIX . $this->orderId;
        
        try {
            // Get simulation data
            $simulationData = Cache::get($cacheKey);
            
            if (!$simulationData || $simulationData['status'] !== 'running') {
                Log::info("Simulation stopped or not found", ['order_id' => $this->orderId]);
                return;
            }

            $order = Order::find($this->orderId);
            if (!$order) {
                Log::error("Order not found for simulation", ['order_id' => $this->orderId]);
                return;
            }

            $currentPoint = $simulationData['current_point'];
            $route = $simulationData['route'];
            $totalPoints = $simulationData['total_points'];

            // Check if simulation is complete
            if ($currentPoint >= $totalPoints - 1) {
                $this->completeSimulation($order, $cacheKey);
                return;
            }

            // Get current position
            $position = $route[$currentPoint];
            
            // Calculate progress and metrics
            $progressPercentage = round(($currentPoint / ($totalPoints - 1)) * 100, 1);
            $remainingPoints = $totalPoints - $currentPoint - 1;
            $distanceRemaining = $this->calculateRemainingDistance($route, $currentPoint);
            $estimatedDuration = $this->calculateEstimatedDuration($remainingPoints, $simulationData['interval_ms']);

            // Send WebSocket update
            $this->broadcastPositionUpdate($order, $position, $progressPercentage, $distanceRemaining, $estimatedDuration);

            // Update simulation data
            $simulationData['current_point'] = $currentPoint + 1;
            $simulationData['last_update'] = now()->toISOString();
            Cache::put($cacheKey, $simulationData, now()->addMinutes(10));

            // Schedule next update
            if ($currentPoint + 1 < $totalPoints) {
                ProcessDeliverySimulation::dispatch($this->orderId, $this->simulationId)
                    ->delay(now()->addMilliseconds($simulationData['interval_ms']));
            }

            Log::debug("Simulation point updated", [
                'order_id' => $this->orderId,
                'point' => $currentPoint + 1,
                'total' => $totalPoints,
                'progress' => $progressPercentage,
            ]);

        } catch (\Exception $e) {
            Log::error("Simulation processing error", [
                'order_id' => $this->orderId,
                'simulation_id' => $this->simulationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Clean up failed simulation
            Cache::forget($cacheKey);
        }
    }

    /**
     * Broadcast position update via WebSocket
     */
    private function broadcastPositionUpdate(Order $order, array $position, float $progress, float $distance, int $duration): void
    {
        $eventData = [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'driver_lat' => $position['lat'],
            'driver_lng' => $position['lng'],
            'driver_position' => [
                'lat' => $position['lat'],
                'lng' => $position['lng'],
            ],
            'progress_percentage' => $progress,
            'distance_remaining' => $distance,
            'estimated_duration' => $duration,
            'current_speed' => $this->calculateCurrentSpeed($distance, $duration),
            'timestamp' => now()->toISOString(),
            'simulation' => true, // Flag to identify simulated data
            'driver_name' => 'Simulateur Demo',
        ];

        // Broadcast to specific order channel
        broadcast(new DeliveryPositionUpdated($eventData, $order->order_number))->toOthers();

        Log::debug("Position broadcasted", [
            'order_number' => $order->order_number,
            'progress' => $progress,
            'channel' => "delivery-{$order->order_number}",
        ]);
    }

    /**
     * Complete the simulation
     */
    private function completeSimulation(Order $order, string $cacheKey): void
    {
        // Send final position update
        $simulationData = Cache::get($cacheKey);
        $finalPosition = end($simulationData['route']);
        
        $this->broadcastPositionUpdate($order, $finalPosition, 100.0, 0.0, 0);

        // Mark simulation as completed
        $simulationData['status'] = 'completed';
        $simulationData['completed_at'] = now()->toISOString();
        Cache::put($cacheKey, $simulationData, now()->addHours(1)); // Keep for history

        Log::info("Delivery simulation completed", [
            'order_id' => $this->orderId,
            'order_number' => $order->order_number,
            'duration' => now()->diffInSeconds($simulationData['started_at']),
        ]);
    }

    /**
     * Calculate remaining distance (simplified calculation)
     */
    private function calculateRemainingDistance(array $route, int $currentPoint): float
    {
        $totalPoints = count($route);
        $remainingPoints = $totalPoints - $currentPoint - 1;
        
        // Simplified: assume 5km total route, calculate proportionally
        $estimatedTotalDistance = 5.0; // km
        return round($estimatedTotalDistance * ($remainingPoints / $totalPoints), 2);
    }

    /**
     * Calculate estimated duration in minutes
     */
    private function calculateEstimatedDuration(int $remainingPoints, int $intervalMs): int
    {
        return round(($remainingPoints * $intervalMs) / (1000 * 60)); // Convert to minutes
    }

    /**
     * Calculate current speed in km/h
     */
    private function calculateCurrentSpeed(float $distanceKm, int $durationMinutes): int
    {
        if ($durationMinutes <= 0) return 0;
        
        $speedKmh = ($distanceKm / $durationMinutes) * 60;
        return round(max(20, min(60, $speedKmh))); // Realistic speed between 20-60 km/h
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Delivery simulation job failed", [
            'order_id' => $this->orderId,
            'simulation_id' => $this->simulationId,
            'error' => $exception->getMessage(),
        ]);

        // Clean up
        Cache::forget(self::CACHE_PREFIX . $this->orderId);
    }
}