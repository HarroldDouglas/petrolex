<?php

namespace Tests\Unit\Services;

use App\DTOs\RouteDTO;
use App\Services\GoogleMapsService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleMapsServiceTest extends TestCase
{
    private GoogleMapsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GoogleMapsService('fake-api-key');
    }

    public function test_calculate_route_successful_response(): void
    {
        // Mock successful Google Maps API response
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'OK',
                'routes' => [
                    [
                        'legs' => [
                            [
                                'duration' => ['value' => 420], // 7 minutes
                                'distance' => ['value' => 3000], // 3000 meters
                            ],
                        ],
                        'overview_polyline' => [
                            'points' => 'fake_polyline_encoded_string',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $result = $this->service->calculateRoute(11.502, 3.848, 11.520, 3.860);

        $this->assertInstanceOf(RouteDTO::class, $result);
        $this->assertEquals(420, $result->duration);
        $this->assertEquals(3.0, $result->distance); // 3000m -> 3km
        $this->assertNotNull($result->geometry);
        $this->assertEquals('fake_polyline_encoded_string', $result->geometry['polyline']);

        // Verify API request was made correctly
        Http::assertSent(function (Request $request) {
            $url = $request->url();
            parse_str(parse_url($url, PHP_URL_QUERY), $params);

            return str_contains($url, 'maps.googleapis.com/maps/api/directions/json') &&
                   $params['origin'] === '3.848,11.502' &&
                   $params['destination'] === '3.86,11.52' &&
                   $params['key'] === 'fake-api-key' &&
                   $params['mode'] === 'driving' &&
                   $params['language'] === 'fr';
        });
    }

    public function test_calculate_route_api_failure_returns_fallback(): void
    {
        // Mock API failure
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'REQUEST_DENIED',
                'error_message' => 'Invalid API key',
            ], 403),
        ]);

        $result = $this->service->calculateRoute(11.502, 3.848, 11.520, 3.860);

        $this->assertInstanceOf(RouteDTO::class, $result);
        $this->assertGreaterThan(0, $result->duration);
        $this->assertGreaterThan(0, $result->distance);
        $this->assertNull($result->geometry); // Fallback doesn't include geometry
    }

    public function test_calculate_route_network_error_returns_fallback(): void
    {
        // Mock network error
        Http::fake([
            'maps.googleapis.com/*' => Http::response('', 500),
        ]);

        $result = $this->service->calculateRoute(11.502, 3.848, 11.520, 3.860);

        $this->assertInstanceOf(RouteDTO::class, $result);
        $this->assertGreaterThan(0, $result->duration);
        $this->assertGreaterThan(0, $result->distance);
        $this->assertNull($result->geometry);
    }

    public function test_calculate_route_no_routes_found_returns_fallback(): void
    {
        // Mock response with no routes
        Http::fake([
            'maps.googleapis.com/*' => Http::response([
                'status' => 'ZERO_RESULTS',
                'routes' => [],
            ], 200),
        ]);

        $result = $this->service->calculateRoute(11.502, 3.848, 11.520, 3.860);

        $this->assertInstanceOf(RouteDTO::class, $result);
        $this->assertGreaterThan(0, $result->duration);
        $this->assertGreaterThan(0, $result->distance);
        $this->assertNull($result->geometry);
    }

    public function test_fallback_distance_calculation(): void
    {
        // Test fallback calculation with known coordinates
        // Yaoundé coordinates (roughly 2km apart)
        Http::fake([
            'maps.googleapis.com/*' => Http::response('', 500), // Force fallback
        ]);

        $result = $this->service->calculateRoute(
            11.502, 3.848, // Yaoundé center
            11.520, 3.860  // ~2km away
        );

        // Should be roughly 2-3 km distance
        $this->assertGreaterThan(1.5, $result->distance);
        $this->assertLessThan(4.0, $result->distance);

        // Duration should be reasonable (based on 30 km/h)
        $expectedDuration = ($result->distance / 30) * 3600;
        $this->assertEqualsWithDelta($expectedDuration, $result->duration, 60); // 1 minute tolerance
    }

    public function test_calculate_route_with_same_coordinates(): void
    {
        // Same start and end point
        Http::fake([
            'maps.googleapis.com/*' => Http::response('', 500), // Force fallback
        ]);

        $result = $this->service->calculateRoute(11.502, 3.848, 11.502, 3.848);

        $this->assertEquals(0.0, $result->distance);
        $this->assertEquals(0, $result->duration);
    }
}
