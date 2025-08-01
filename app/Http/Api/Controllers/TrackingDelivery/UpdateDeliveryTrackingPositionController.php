<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\Enums\DeliveryTrackingStatus;
use App\Events\DeliveryPositionUpdated;
use App\Http\Api\Requests\TrackingDelivery\UpdateDeliveryTrackingPositionRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class UpdateDeliveryTrackingPositionController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository
    ) {}

    /**
     * Update a delivery's position.
     *
     * Route: PATCH /api/tracking/delivery/{orderNumber}/position
     * Name: tracking.delivery.position.update
     */
    public function __invoke(UpdateDeliveryTrackingPositionRequest $request, int $orderId): ApiResponse
    {
        $deliveryTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

        if (! $deliveryTracking) {
            return DeliveryTrackingResponse::error('Delivery tracking not found.', null, Response::HTTP_NOT_FOUND);
        }

        $order = $deliveryTracking->order;

        if (! $order) {
            return DeliveryTrackingResponse::error('Order not found for this tracking.', null, Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validated();
        $driverLng = (float) $validated['driver_lng'];
        $driverLat = (float) $validated['driver_lat'];

        if ($order->destination_lng === null || $order->destination_lat === null) {
            return DeliveryTrackingResponse::error('Order destination coordinates are missing.', Response::HTTP_BAD_REQUEST);
        }

        $routeData = $this->deliveryTrackingService->calculateRoute(
            $driverLng,
            $driverLat,
            (float) $order->destination_lng,
            (float) $order->destination_lat
        );

        $deliveryTracking = $this->deliveryTrackingRepository->update(
            $deliveryTracking,
            [
                'driver_lat' => $driverLat,
                'driver_lng' => $driverLng,
                'estimated_duration' => $routeData->duration,
                'distance_remaining' => $routeData->distance,
                'status' => DeliveryTrackingStatus::IN_PROGRESS(),
            ]
        );

        broadcast(new DeliveryPositionUpdated($deliveryTracking));

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery position updated successfully.'
        );
    }
}
