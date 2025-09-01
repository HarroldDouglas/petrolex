<?php

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\Http\Api\Requests\TrackingDelivery\UpdateDeliveryTrackingPositionRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;

class UpdateDeliveryTrackingPositionController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService
    ) {}

    /**
     * Update delivery tracking position.
     *
     * Route: PUT /api/delivery-tracking/{orderId}/position
     * Name: api.delivery-tracking.position.update
     */
    public function __invoke(UpdateDeliveryTrackingPositionRequest $request, int $orderId): ApiResponse
    {
        $updatedTracking = $this->deliveryTrackingService->updatePosition($orderId, $request);

        return DeliveryTrackingResponse::make(
            $updatedTracking,
            'Delivery position updated successfully.'
        );
    }
}