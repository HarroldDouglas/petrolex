<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RouteCalculatorInterface;
use App\DTOs\RouteDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class GoogleMapsService implements RouteCalculatorInterface
{
    private const BASE_URL = 'https://maps.googleapis.com/maps/api/directions/json';

    public function __construct(
        private readonly string $apiKey
    ) {}

    public function calculateRoute(float $fromLng, float $fromLat, float $toLng, float $toLat): RouteDTO
    {
        try {
            $response = Http::get(self::BASE_URL, [
                'origin' => "{$fromLat},{$fromLng}",
                'destination' => "{$toLat},{$toLng}",
                'key' => $this->apiKey,
                'mode' => 'driving',
                'language' => 'fr',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK' && ! empty($data['routes'])) {
                    $route = $data['routes'][0];
                    $leg = $route['legs'][0];

                    return new RouteDTO(
                        duration: $leg['duration']['value'], // seconds
                        distance: (float) ($leg['distance']['value'] / 1000), // convert to km
                        geometry: isset($route['overview_polyline']['points']) ?
                            ['polyline' => $route['overview_polyline']['points']] : null
                    );
                }
            }

            Log::warning('Google Maps API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::error('Google Maps API error', ['error' => $e->getMessage()]);
        }

        // Fallback: calculate simple distance
        return $this->fallbackRoute($fromLat, $fromLng, $toLat, $toLng);
    }

    private function fallbackRoute(float $fromLat, float $fromLng, float $toLat, float $toLng): RouteDTO
    {
        $distance = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);
        $duration = (int) (($distance / 30) * 3600); // 30 km/h average speed

        return new RouteDTO(
            duration: $duration,
            distance: $distance,
            geometry: null
        );
    }

    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
