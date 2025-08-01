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
use Illuminate\Support\Facades\Log;

final class UpdateDeliveryTrackingPositionController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository
    ) {}

    /**
     * Update a delivery's position.
     *
     * Route: PATCH /api/tracking/delivery/{orderId}/position
     * Name: tracking.delivery.position.update
     */
    public function __invoke(UpdateDeliveryTrackingPositionRequest $request, int $orderId): ApiResponse
    {
        Log::info('Attempting to update delivery tracking position for order ID: ' . $orderId);
        $deliveryTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

        if (! $deliveryTracking) {
            Log::warning('Delivery tracking not found for order ID: ' . $orderId);
            return DeliveryTrackingResponse::error('Delivery tracking not found.', null, Response::HTTP_NOT_FOUND);
        }

        $order = $deliveryTracking->order;

        if (! $order) {
            Log::error('Order not found for tracking ID: ' . $deliveryTracking->id);
            return DeliveryTrackingResponse::error('Order not found for this tracking.', null, Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validated();
        $driverLng = (float) $validated['driver_lng'];
        $driverLat = (float) $validated['driver_lat'];
        $currentSpeed = (float) ($validated['current_speed'] ?? 0.0);

        Log::info('Updating position for tracking ID ' . $deliveryTracking->id . ': Lat=' . $driverLat . ', Lng=' . $driverLng . ', Speed=' . $currentSpeed);

        if ($order->destination_lng === null || $order->destination_lat === null) {
            Log::error('Order destination coordinates missing for order ID: ' . $order->id);
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
                'current_speed' => $currentSpeed,
            ]
        );

        Log::info('Delivery position updated successfully for tracking ID: ' . $deliveryTracking->id);
        broadcast(new DeliveryPositionUpdated($deliveryTracking));

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery position updated successfully.'
        );
    }
}