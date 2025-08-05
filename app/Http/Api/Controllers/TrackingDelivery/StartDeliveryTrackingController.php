<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\DTOs\DeliveryTracking\CreateDeliveryTrackingDTO;
use App\DTOs\Order\UpdateOrderDTO;
use App\Enums\DeliveryTrackingStatus;
use App\Enums\OrderStatus;
use App\Events\DeliveryPositionUpdated;
use App\Events\DeliveryStatusUpdated;
use App\Http\Api\Requests\TrackingDelivery\StartDeliveryTrackingRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class StartDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderService $orderService
    ) {}

    /**
     * Start a delivery.
     *
     * Route: POST /api/tracking/delivery/{orderId}/start
     * Name: tracking.delivery.start
     */
    public function __invoke(StartDeliveryTrackingRequest $request, int $orderId): ApiResponse
    {
        Log::info('=== START DELIVERY TRACKING CONTROLLER CALLED === Order ID: '.$orderId);
        Log::info('Attempting to start delivery tracking for order ID: '.$orderId);

        /** @var Order $order */
        $order = $this->orderService->find($orderId);

        if (! $order) {
            Log::warning('Order not found for ID: '.$orderId);

            return DeliveryTrackingResponse::error('Order not found.', null, Response::HTTP_NOT_FOUND);
        }

        $existingTracking = $this->deliveryTrackingRepository->findByOrder($orderId);
        if ($existingTracking && $existingTracking->status->value === 'completed') {
            Log::warning('Delivery tracking already completed for order ID: '.$orderId);

            return DeliveryTrackingResponse::error('Delivery tracking is already completed.', null, Response::HTTP_CONFLICT);
        }

        $driverLat = $request->validated()['driver_lat'];
        $driverLng = $request->validated()['driver_lng'];

        if ($existingTracking) {
            // CORRECTION CRITIQUE: Capturer le statut précédent
            $previousStatus = $existingTracking->status->value;

            if (! $order->destination_lat || ! $order->destination_lng) {
                Log::error('Order destination coordinates are missing for order ID: '.$orderId);

                return DeliveryTrackingResponse::error('Order destination coordinates are missing.', null, Response::HTTP_BAD_REQUEST);
            }

            $routeData = $this->deliveryTrackingService->calculateRoute(
                $driverLng,
                $driverLat,
                (float) $order->destination_lng,
                (float) $order->destination_lat
            );

            // Mettre à jour le tracking existant
            $existingTracking->update([
                'status' => DeliveryTrackingStatus::STARTED(),
                'driver_lat' => $driverLat,
                'driver_lng' => $driverLng,
                'estimated_duration' => $routeData->duration,
                'distance_remaining' => $routeData->distance,
                'route_geometry' => $routeData->geometry,
                'started_at' => now(),
            ]);

            Log::info('Existing delivery tracking updated and started for order ID: '.$orderId.'. Tracking ID: '.$existingTracking->id);

            // AMÉLIORATION CRITIQUE: Déclencher les événements appropriés
            broadcast(new DeliveryPositionUpdated($existingTracking->fresh()));
            broadcast(new DeliveryStatusUpdated($existingTracking->fresh(), $previousStatus));

            $this->orderService->update(
                $order,
                (new UpdateOrderDTO(status: OrderStatus::PROCESSING()))->toArrayFiltered()
            );

            return DeliveryTrackingResponse::make(
                $existingTracking->fresh()->load('order.customer', 'order.deliveryAddress'),
                'Delivery started successfully with existing tracking.'
            );
        }

        // Créer un nouveau tracking si aucun n'existe
        if (! $order->destination_lat || ! $order->destination_lng) {
            Log::error('Order destination coordinates are missing for order ID: '.$orderId);

            return DeliveryTrackingResponse::error('Order destination coordinates are missing.', null, Response::HTTP_BAD_REQUEST);
        }

        $routeData = $this->deliveryTrackingService->calculateRoute(
            $driverLng,
            $driverLat,
            (float) $order->destination_lng,
            (float) $order->destination_lat
        );

        $createDeliveryTrackingDTO = new CreateDeliveryTrackingDTO(
            order_id: $orderId,
            status: DeliveryTrackingStatus::STARTED(),
            driver_lat: $driverLat,
            driver_lng: $driverLng,
            estimated_duration: $routeData->duration,
            distance_remaining: $routeData->distance,
            route_geometry: $routeData->geometry,
            started_at: now()
        );

        $deliveryTracking = $this->deliveryTrackingRepository->create($createDeliveryTrackingDTO->toArray());

        Log::info('New delivery tracking created and started for order ID: '.$orderId.'. Tracking ID: '.$deliveryTracking->id);

        // AMÉLIORATION CRITIQUE: Déclencher les événements pour le nouveau tracking
        broadcast(new DeliveryPositionUpdated($deliveryTracking->fresh()));
        broadcast(new DeliveryStatusUpdated($deliveryTracking->fresh(), null));

        $this->orderService->update(
            $order,
            (new UpdateOrderDTO(status: OrderStatus::PROCESSING()))->toArrayFiltered()
        );

        return DeliveryTrackingResponse::make(
            $deliveryTracking->load('order.customer', 'order.deliveryAddress'),
            'Delivery started successfully.'
        );
    }
}
