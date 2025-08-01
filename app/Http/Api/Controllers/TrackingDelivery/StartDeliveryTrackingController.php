<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\DTOs\Order\UpdateOrderDTO;
use App\Enums\DeliveryTrackingStatus;
use App\Enums\OrderStatus;
use App\Events\DeliveryPositionUpdated;
use App\Http\Api\Requests\TrackingDelivery\StartDeliveryTrackingRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Services\Order\OrderService;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

final class StartDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderService $orderService,
    ) {}

    /**
     * Start a delivery.
     *
     * Route: POST /api/tracking/delivery/{orderId}/start
     * Name: tracking.delivery.start
     */
    public function __invoke(StartDeliveryTrackingRequest $request, int $orderId): ApiResponse
    {
        Log::info('Attempting to start delivery tracking for order ID: ' . $orderId);
        /** @var Order $order */
        $order = $this->orderService->find($orderId);

        if (! $order) {
            Log::warning('Order not found for ID: ' . $orderId);
            return DeliveryTrackingResponse::error('Order not found.', null, Response::HTTP_NOT_FOUND);
        }

        $existingTracking = $this->deliveryTrackingRepository->findByOrder($orderId);
        if ($existingTracking && $existingTracking->status->value !== 'completed') {
            Log::warning('Delivery tracking already exists for order ID: ' . $orderId . ' and is not completed.');
            return DeliveryTrackingResponse::error('Delivery tracking already exists for this order.', null, Response::HTTP_CONFLICT);
        }

        $validated = $request->validated();
        $driverLng = (float) $validated['driver_lng'];
        $driverLat = (float) $validated['driver_lat'];

        Log::info('Driver coordinates for order ID ' . $orderId . ': Lat=' . $driverLat . ', Lng=' . $driverLng);

        if ($order->destination_lng === null || $order->destination_lat === null) {
            Log::error('Order destination coordinates missing for order ID: ' . $orderId);
            return DeliveryTrackingResponse::error('Order destination coordinates are missing.', null, Response::HTTP_BAD_REQUEST);
        }

        $routeData = $this->deliveryTrackingService->calculateRoute(
            $driverLng,
            $driverLat,
            (float) $order->destination_lng,
            (float) $order->destination_lat
        );

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

        Log::info('Delivery tracking started successfully for order ID: ' . $orderId . '. Tracking ID: ' . $deliveryTracking->id);
        broadcast(new DeliveryPositionUpdated($deliveryTracking));
        $this->orderService->update(
            $order, 
            (new UpdateOrderDTO(status: OrderStatus::PROCESSING()))->toArrayFiltered()
        );

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery started successfully.'
        );
    }
}