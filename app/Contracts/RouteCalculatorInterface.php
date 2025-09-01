<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\RouteDTO;

interface RouteCalculatorInterface
{
    /**
     * Calculate route between two geographical points
     *
     * @param float $fromLng Starting longitude
     * @param float $fromLat Starting latitude
     * @param float $toLng Destination longitude
     * @param float $toLat Destination latitude
     * @return RouteDTO Route data containing duration, distance and geometry
     */
    public function calculateRoute(float $fromLng, float $fromLat, float $toLng, float $toLat): RouteDTO;
}
