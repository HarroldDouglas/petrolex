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
use App\Models\DeliveryTracking;
use App\Models\Order;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class StartDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderService $orderService,
    ) {}

    /**
     * Start a delivery tracking.
     *
     * @throws \Exception
     */
    public function __invoke(StartDeliveryTrackingRequest $request, int $orderId): ApiResponse
    {
        Log::info('Starting delivery tracking', ['order_id' => $orderId]);

        return DB::transaction(function () use ($request, $orderId) {
            $order = $this->getValidatedOrder($orderId);
            $existingTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

            if ($this->isTrackingAlreadyCompleted($existingTracking)) {
                return $this->createConflictResponse();
            }

            $coordinatesData = $this->extractCoordinatesData($request);
            $routeData = $this->calculateDeliveryRoute($order, $coordinatesData);

            $tracking = $this->processDeliveryTracking(
                $existingTracking,
                $orderId,
                $coordinatesData,
                $routeData
            );

            $this->updateOrderToProcessing($order);
            $this->broadcastDeliveryUpdate($tracking);

            Log::info('Delivery tracking started successfully', ['order_id' => $orderId]);

            return $this->createSuccessResponse($tracking, $existingTracking !== null);
        });
    }

    private function getValidatedOrder(int $orderId): Order
    {
        $order = $this->orderService->find($orderId);

        if (! $order) {
            Log::warning('Order not found', ['order_id' => $orderId]);
            throw new \InvalidArgumentException('Order not found');
        }

        /** @var Order */
        $order->load('deliveryAddress');
        return $order;
    }

    private function isTrackingAlreadyCompleted(?DeliveryTracking $tracking): bool
    {
        return $tracking?->status->value === DeliveryTrackingStatus::DELIVERED()->value;
    }

    private function createConflictResponse(): ApiResponse
    {
        return DeliveryTrackingResponse::error(
            'Delivery tracking already completed for this order.',
            null,
            Response::HTTP_CONFLICT
        );
    }

    /**
     * @return array{lat: float, lng: float}
     */
    private function extractCoordinatesData(StartDeliveryTrackingRequest $request): array
    {
        $validated = $request->validated();

        return [
            'lat' => (float) $validated['driver_lat'],
            'lng' => (float) $validated['driver_lng'],
        ];
    }

    private function calculateDeliveryRoute(Order $order, array $coordinates): object
    {
        if ($this->hasInvalidDestinationCoordinates($order)) {
            Log::error('Missing destination coordinates', ['order_id' => $order->id]);
            throw new \InvalidArgumentException('Order destination coordinates are missing');
        }

        return $this->deliveryTrackingService->calculateRoute(
            $coordinates['lng'],
            $coordinates['lat'],
            (float) $order->destination_lng,
            (float) $order->destination_lat
        );
    }

    private function hasInvalidDestinationCoordinates(Order $order): bool
    {
        return $order->destination_lng === null || $order->destination_lat === null;
    }

    private function processDeliveryTracking(
        ?DeliveryTracking $existingTracking,
        int $orderId,
        array $coordinates,
        object $routeData
    ): DeliveryTracking {
        return $existingTracking
            ? $this->updateExistingTracking($existingTracking, $coordinates, $routeData)
            : $this->createNewTracking($orderId, $coordinates, $routeData);
    }

    private function updateExistingTracking(
        DeliveryTracking $tracking,
        array $coordinates,
        object $routeData
    ): DeliveryTracking {
        Log::info('Updating existing tracking', ['tracking_id' => $tracking->id]);

        $tracking->update([
            'status' => DeliveryTrackingStatus::STARTED(),
            'driver_lat' => $coordinates['lat'],
            'driver_lng' => $coordinates['lng'],
            'estimated_duration' => $routeData->duration,
            'distance_remaining' => $routeData->distance,
            'route_geometry' => $routeData->geometry,
            'started_at' => now(),
            'total_distance' => $tracking->total_distance ?? $routeData->distance,
        ]);

        return $tracking;
    }

    private function createNewTracking(
        int $orderId,
        array $coordinates,
        object $routeData
    ): DeliveryTracking {
        Log::info('Creating new tracking', ['order_id' => $orderId]);

        return $this->deliveryTrackingRepository->create([
            'order_id' => $orderId,
            'status' => DeliveryTrackingStatus::STARTED(),
            'driver_lat' => $coordinates['lat'],
            'driver_lng' => $coordinates['lng'],
            'estimated_duration' => $routeData->duration,
            'distance_remaining' => $routeData->distance,
            'total_distance' => $routeData->distance,
            'route_geometry' => $routeData->geometry,
            'started_at' => now(),
        ]);
    }

    private function updateOrderToProcessing(Order $order): void
    {
        $updateDto = new UpdateOrderDTO(status: OrderStatus::PROCESSING());
        $this->orderService->update($order, $updateDto->toArrayFiltered());
    }

    private function broadcastDeliveryUpdate(DeliveryTracking $tracking): void
    {
        broadcast(new DeliveryPositionUpdated($tracking));
    }

    private function createSuccessResponse(DeliveryTracking $tracking, bool $wasRestarted): ApiResponse
    {
        $message = $wasRestarted ? 'Delivery restarted successfully.' : 'Delivery started successfully.';
        $freshTracking = $tracking->fresh()->load('order.customer', 'order.deliveryAddress');

        return DeliveryTrackingResponse::make($freshTracking, $message);
    }
}
