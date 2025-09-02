<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class DeliveryProgressHelper
{
    /**
     * Calculate delivery progress percentage based on total distance and remaining distance
     *
     * @param  float|null  $totalDistance  Total distance in kilometers
     * @param  float|null  $remainingDistance  Remaining distance in kilometers
     * @param  bool  $enableLogging  Whether to log the calculation details
     * @return float|null Progress percentage (0-100) or null if calculation is not possible
     */
    public static function calculateProgressPercentage(
        ?float $totalDistance,
        ?float $remainingDistance,
        bool $enableLogging = false
    ): ?float {
        if (! $totalDistance || ! $remainingDistance || $totalDistance <= 0) {
            if ($enableLogging) {
                Log::info('Cannot calculate progress - missing or invalid data', [
                    'total_distance' => $totalDistance,
                    'remaining_distance' => $remainingDistance,
                ]);
            }

            return null;
        }

        $distanceTraveled = max(0, $totalDistance - $remainingDistance);
        $progress = ($distanceTraveled / $totalDistance) * 100;
        $finalProgress = max(0, min(100, $progress));

        if ($enableLogging) {
            Log::info('Progress calculated', [
                'total_distance' => $totalDistance,
                'remaining_distance' => $remainingDistance,
                'distance_traveled' => $distanceTraveled,
                'progress_percentage' => $finalProgress,
            ]);
        }

        return $finalProgress;
    }

    /**
     * Calculate delivery progress percentage with automatic logging
     *
     * @param  float|null  $totalDistance  Total distance in kilometers
     * @param  float|null  $remainingDistance  Remaining distance in kilometers
     * @return float|null Progress percentage (0-100) or null if calculation is not possible
     */
    public static function calculateProgressPercentageWithLogging(
        ?float $totalDistance,
        ?float $remainingDistance
    ): ?float {
        return self::calculateProgressPercentage($totalDistance, $remainingDistance, true);
    }
}
