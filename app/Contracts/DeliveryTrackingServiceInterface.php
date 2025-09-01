<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\RouteDTO;
use App\Http\Api\Requests\TrackingDelivery\UpdateDeliveryTrackingPositionRequest;
use App\Models\DeliveryTracking;

interface DeliveryTrackingServiceInterface
{
    public function calculateRoute(float $fromLng, float $fromLat, float $toLng, float $toLat): RouteDTO;
    
    public function updatePosition(int $orderId, UpdateDeliveryTrackingPositionRequest $request): DeliveryTracking;
}
