<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\RouteDTO;

interface DeliveryTrackingServiceInterface
{
    public function calculateRoute(float $fromLng, float $fromLat, float $toLng, float $toLat): RouteDTO;
}
