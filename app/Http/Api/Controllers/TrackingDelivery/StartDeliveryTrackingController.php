<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\Enums\DeliveryTrackingStatus;
use App\Events\DeliveryPositionUpdated;
use App\Http\Api\Requests\TrackingDelivery\StartDeliveryTrackingRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class StartDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository
    ) {}

    /**
     * Start a delivery.
     *
     * Route: POST /api/tracking/delivery/{orderNumber}/start
     * Name: tracking.delivery.start
     */
    public function __invoke(StartDeliveryTrackingRequest $request, int $orderId): ApiResponse
    {
        $order = \App\Models\Order::find($orderId);

        if (! $order) {
            return DeliveryTrackingResponse::error('Order not found.', null, Response::HTTP_NOT_FOUND);
        }

        $existingTracking = $this->deliveryTrackingRepository->findByOrder($orderId);
        if ($existingTracking && $existingTracking->status->value !== 'completed') {
            return DeliveryTrackingResponse::error('Delivery tracking already exists for this order.', null, Response::HTTP_CONFLICT);
        }

        $validated = $request->validated();
        $driverLng = (float) $validated['driver_lng'];
        $driverLat = (float) $validated['driver_lat'];

        if ($order->destination_lng === null || $order->destination_lat === null) {
            return DeliveryTrackingResponse::error('Order destination coordinates are missing.', null, Response::HTTP_BAD_REQUEST);
        }

        $routeData = $this->deliveryTrackingService->calculateRoute(
            $driverLng,
            $driverLat,
            (float) $order->destination_lng,
            (float) $order->destination_lat
        );

        // TODO: Think if this should not be moved to a listener
        $deliveryTracking = $this->deliveryTrackingRepository->create([
            'order_id' => $orderId,
            'status' => DeliveryTrackingStatus::STARTED(),
            'driver_lat' => $driverLat,
            'driver_lng' => $driverLng,
            'estimated_duration' => $routeData->duration,
            'distance_remaining' => $routeData->distance,
            'route_geometry' => $routeData->geometry,
            'started_at' => now(),
        ]);

        broadcast(new DeliveryPositionUpdated($deliveryTracking));

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery started successfully.'
        );
    }
}
