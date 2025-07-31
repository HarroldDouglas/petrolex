<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\DTOs\RouteDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class MapboxService implements DeliveryTrackingServiceInterface
{
    private string $accessToken;

    public function __construct()
    {
        $token = config('services.mapbox.token');
        if (!is_string($token)) {
            throw new \InvalidArgumentException('Mapbox access token is not a string.');
        }
        $this->accessToken = $token;
    }

    public function calculateRoute(float $fromLng, float $fromLat, float $toLng, float $toLat): RouteDTO
    {
        if (empty($this->accessToken)) {
            Log::error('Mapbox access token is not configured.');
            return $this->fallbackRoute();
        }

        try {
            $response = Http::get("https://api.mapbox.com/directions/v5/mapbox/driving/{$fromLng},{$fromLat};{$toLng},{$toLat}", [
                'access_token' => $this->accessToken,
                'steps' => 'true',
                'geometries' => 'geojson',
                'overview' => 'full'
            ]);

            if ($response->failed()) {
                Log::error('Mapbox API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return $this->fallbackRoute();
            }

            /** @var array<string, mixed>|null $data */
            $data = $response->json();

            if (isset($data['routes']) && is_array($data['routes']) && !empty($data['routes'])) {
                $route = $data['routes'][0];
                if (is_array($route) && 
                    isset($route['duration'], $route['distance'], $route['geometry']) &&
                    is_numeric($route['duration']) &&
                    is_numeric($route['distance'])
                ) {
                    /** @var array<string, mixed>|null $geometry */
                    $geometry = is_array($route['geometry']) ? $route['geometry'] : null;
                    return new RouteDTO(
                        duration: (int) ceil((float)$route['duration'] / 60),
                        distance: round((float)$route['distance'] / 1000, 2),
                        geometry: $geometry
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Mapbox API Error: ' . $e->getMessage());
        }

        return $this->fallbackRoute();
    }

    private function fallbackRoute(): RouteDTO
    {
        return new RouteDTO(
            duration: 30, // estimation basique
            distance: 5.0,
            geometry: null
        );
    }
}
