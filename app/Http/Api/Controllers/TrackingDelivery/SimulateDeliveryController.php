<?php

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Events\DeliveryPositionUpdated;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Simulate Delivery Controller - HIDDEN ENDPOINT FOR PRODUCTION DEMOS
 * 
 * This endpoint is NOT documented in Swagger and is used exclusively for 
 * demonstrating the real-time tracking system to clients.
 * 
 * DO NOT expose this endpoint in production API documentation.
 */
class SimulateDeliveryController extends Controller
{
    // Configuration constants for simulation
    private const SIMULATION_DURATION_MINUTES = 5; // Total simulation time
    private const UPDATE_INTERVAL_SECONDS = 3;     // Position update frequency
    private const CACHE_PREFIX = 'delivery_simulation_';
    
    // Yaoundé area coordinates for realistic simulation
    private const YAOUNEDE_BOUNDS = [
        'center' => ['lat' => 3.848, 'lng' => 11.502],
        'radius' => 0.05, // ~5km radius
    ];

    /**
     * Start delivery simulation for demonstration
     * 
     * @param Request $request
     * @param int $orderId
     * @return JsonResponse
     */
    public function __invoke(Request $request, int $orderId): JsonResponse
    {
        try {
            // For GET requests, get parameters from query string
            $params = $request->method() === 'GET' ? $request->query() : $request->all();
            
            // Validate the request
            $validator = Validator::make($params, [
                'duration_minutes' => 'nullable|integer|min:1|max:30',
                'speed_multiplier' => 'nullable|numeric|min:0.5|max:5',
                'start_lat' => 'nullable|numeric|between:-90,90',
                'start_lng' => 'nullable|numeric|between:-180,180',
                'end_lat' => 'nullable|numeric|between:-90,90',
                'end_lng' => 'nullable|numeric|between:-180,180',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    '_metadata' => [
                        'success' => false,
                        'message' => 'Validation error',
                        'errors' => $validator->errors(),
                    ],
                ], 422);
            }

            // Find the order
            $order = Order::find($orderId);
            if (!$order) {
                return response()->json([
                    '_metadata' => [
                        'success' => false,
                        'message' => 'Order not found',
                    ],
                ], 404);
            }

            // Check if simulation is already running
            if (Cache::has(self::CACHE_PREFIX . $orderId)) {
                return response()->json([
                    '_metadata' => [
                        'success' => false,
                        'message' => 'Simulation already running for this order',
                    ],
                ], 409);
            }

            // Extract parameters with defaults
            $duration = $params['duration_minutes'] ?? self::SIMULATION_DURATION_MINUTES;
            $speedMultiplier = $params['speed_multiplier'] ?? 1.0;
            
            // Generate realistic coordinates
            $route = $this->generateRealisticRoute(
                $params['start_lat'] ?? null,
                $params['start_lng'] ?? null,
                $params['end_lat'] ?? null,
                $params['end_lng'] ?? null,
                $order
            );

            // Start the simulation
            $simulationId = $this->startSimulation($order, $route, $duration, $speedMultiplier);

            Log::info("Delivery simulation started", [
                'order_id' => $orderId,
                'simulation_id' => $simulationId,
                'duration_minutes' => $duration,
                'route_points' => count($route),
            ]);

            return response()->json([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Delivery simulation started successfully',
                ],
                'data' => [
                    'simulation_id' => $simulationId,
                    'order_id' => $orderId,
                    'order_number' => $order->order_number,
                    'duration_minutes' => $duration,
                    'update_interval_seconds' => self::UPDATE_INTERVAL_SECONDS,
                    'total_route_points' => count($route),
                    'websocket_channel' => "delivery-{$order->order_number}",
                    'estimated_completion' => now()->addMinutes($duration)->toISOString(),
                    'route_preview' => [
                        'start' => $route[0],
                        'end' => $route[count($route) - 1],
                        'total_points' => count($route),
                    ],
                ],
            ]);

        } catch (\Exception $e) {
            Log::error("Delivery simulation error", [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                '_metadata' => [
                    'success' => false,
                    'message' => 'Internal server error during simulation start',
                ],
            ], 500);
        }
    }

    /**
     * Generate realistic delivery route
     */
    private function generateRealisticRoute(?float $startLat, ?float $startLng, ?float $endLat, ?float $endLng, Order $order): array
    {
        // Use provided coordinates or generate realistic ones for Yaoundé
        $start = [
            'lat' => $startLat ?? $this->getRandomCoordinate(self::YAOUNEDE_BOUNDS, 'lat'),
            'lng' => $startLng ?? $this->getRandomCoordinate(self::YAOUNEDE_BOUNDS, 'lng'),
        ];

        $end = [
            'lat' => $endLat ?? $order->delivery_address?->latitude ?? $this->getRandomCoordinate(self::YAOUNEDE_BOUNDS, 'lat'),
            'lng' => $endLng ?? $order->delivery_address?->longitude ?? $this->getRandomCoordinate(self::YAOUNEDE_BOUNDS, 'lng'),
        ];

        // Generate intermediate points for realistic movement
        return $this->generateIntermediatePoints($start, $end, 50); // 50 points for smooth animation
    }

    /**
     * Generate intermediate points between start and end
     */
    private function generateIntermediatePoints(array $start, array $end, int $numPoints): array
    {
        $points = [$start];
        
        for ($i = 1; $i < $numPoints - 1; $i++) {
            $ratio = $i / ($numPoints - 1);
            
            // Linear interpolation with slight random variation for realism
            $lat = $start['lat'] + ($end['lat'] - $start['lat']) * $ratio;
            $lng = $start['lng'] + ($end['lng'] - $start['lng']) * $ratio;
            
            // Add small random variation (±50 meters)
            $lat += (rand(-50, 50) / 1000000);
            $lng += (rand(-50, 50) / 1000000);
            
            $points[] = ['lat' => $lat, 'lng' => $lng];
        }
        
        $points[] = $end;
        return $points;
    }

    /**
     * Get random coordinate within bounds
     */
    private function getRandomCoordinate(array $bounds, string $type): float
    {
        $center = $bounds['center'][$type];
        $radius = $bounds['radius'];
        
        return $center + (rand(-1000, 1000) / 1000) * $radius;
    }

    /**
     * Start the delivery simulation using Laravel's job queue
     */
    private function startSimulation(Order $order, array $route, int $duration, float $speedMultiplier): string
    {
        $simulationId = uniqid('sim_');
        $totalPoints = count($route);
        $intervalMs = ($duration * 60 * 1000) / $totalPoints;
        $adjustedInterval = max(1000, $intervalMs / $speedMultiplier); // Minimum 1 second

        // Store simulation data in cache
        $simulationData = [
            'simulation_id' => $simulationId,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'route' => $route,
            'current_point' => 0,
            'total_points' => $totalPoints,
            'interval_ms' => $adjustedInterval,
            'started_at' => now()->toISOString(),
            'status' => 'running',
        ];

        Cache::put(self::CACHE_PREFIX . $order->id, $simulationData, now()->addMinutes($duration + 5));

        // Dispatch the simulation job
        \App\Jobs\ProcessDeliverySimulation::dispatch($order->id, $simulationId);

        return $simulationId;
    }

    /**
     * Stop a running simulation
     */
    public static function stopSimulation(int $orderId): bool
    {
        $cacheKey = self::CACHE_PREFIX . $orderId;
        
        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            $data['status'] = 'stopped';
            Cache::put($cacheKey, $data, now()->addMinutes(5));
            
            Log::info("Delivery simulation stopped", ['order_id' => $orderId]);
            return true;
        }
        
        return false;
    }

    /**
     * Get simulation status
     */
    public static function getSimulationStatus(int $orderId): ?array
    {
        return Cache::get(self::CACHE_PREFIX . $orderId);
    }
}