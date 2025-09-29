<?php

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Http\Controllers\Controller;
use App\Http\Api\Controllers\TrackingDelivery\SimulateDeliveryController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Simulation Status Controller - HIDDEN ENDPOINT FOR PRODUCTION DEMOS
 * 
 * Allows checking and stopping delivery simulations
 */
class SimulationStatusController extends Controller
{
    private const CACHE_PREFIX = 'delivery_simulation_';

    /**
     * Get simulation status
     */
    public function status(Request $request, int $orderId): JsonResponse
    {
        $simulationData = SimulateDeliveryController::getSimulationStatus($orderId);

        if (!$simulationData) {
            return response()->json([
                '_metadata' => [
                    'success' => false,
                    'message' => 'No simulation found for this order',
                ],
            ], 404);
        }

        return response()->json([
            '_metadata' => [
                'success' => true,
                'message' => 'Simulation status retrieved',
            ],
            'data' => [
                'simulation_id' => $simulationData['simulation_id'],
                'order_id' => $simulationData['order_id'],
                'order_number' => $simulationData['order_number'],
                'status' => $simulationData['status'],
                'current_point' => $simulationData['current_point'] ?? 0,
                'total_points' => $simulationData['total_points'],
                'progress_percentage' => round(($simulationData['current_point'] ?? 0) / $simulationData['total_points'] * 100, 1),
                'started_at' => $simulationData['started_at'],
                'last_update' => $simulationData['last_update'] ?? null,
                'completed_at' => $simulationData['completed_at'] ?? null,
            ],
        ]);
    }

    /**
     * Stop simulation
     */
    public function stop(Request $request, int $orderId): JsonResponse
    {
        $stopped = SimulateDeliveryController::stopSimulation($orderId);

        if (!$stopped) {
            return response()->json([
                '_metadata' => [
                    'success' => false,
                    'message' => 'No active simulation found for this order',
                ],
            ], 404);
        }

        return response()->json([
            '_metadata' => [
                'success' => true,
                'message' => 'Simulation stopped successfully',
            ],
            'data' => [
                'order_id' => $orderId,
                'stopped_at' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * List all active simulations (admin only)
     */
    public function listActive(Request $request): JsonResponse
    {
        // Get all cache keys that match our pattern
        $cacheKeys = [];
        $pattern = self::CACHE_PREFIX . '*';
        
        // Note: This is a simplified version. In production, you might want to use Redis SCAN
        // For now, we'll return a basic structure
        
        return response()->json([
            '_metadata' => [
                'success' => true,
                'message' => 'Active simulations listed',
            ],
            'data' => [
                'active_simulations' => 0, // Would need Redis integration for full implementation
                'note' => 'Use individual status endpoints for specific orders',
            ],
        ]);
    }
}