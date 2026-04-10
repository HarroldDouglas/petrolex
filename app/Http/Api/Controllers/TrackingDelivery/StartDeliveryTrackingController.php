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
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class StartDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderService $orderService,
    ) {}

    public function __invoke(StartDeliveryTrackingRequest $request, int $orderId): ApiResponse
    {
        Log::info('Starting delivery tracking', ['order_id' => $orderId]);

        try {
            return DB::transaction(function () use ($request, $orderId) {
                $order = $this->getValidatedOrder($orderId);
                $this->validateDeliveryPersonAccess($order);
                $this->validateBottlesLinked($order);
                $existingTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

                if ($this->isTrackingCompleted($existingTracking)) {
                    return $this->createConflictResponse();
                }

                $coordinatesData = $this->extractCoordinatesData($request);
                $tracking = $this->ensureTrackingExists($existingTracking, $order, $coordinatesData);

                $this->startTrackingProcess($tracking, $coordinatesData, $order);
                $this->updateOrderStatus($order);
                $this->broadcastDeliveryUpdate($tracking);

                Log::info('Delivery tracking started successfully', ['order_id' => $orderId]);

                return $this->createSuccessResponse($tracking, $existingTracking !== null);
            });
        } catch (\InvalidArgumentException $e) {
            Log::error($e->getMessage(), ['userId' => auth()->id()]);

            return DeliveryTrackingResponse::error(
                $e->getMessage(),
                null,
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }

    private function getValidatedOrder(int $orderId): Order
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->find($orderId);

        if (! $order) {
            Log::warning('Order not found', ['order_id' => $orderId]);
            throw new \InvalidArgumentException('Order not found');
        }

        if (! $order->canBeTracked()) {
            Log::warning('Order cannot be tracked', ['order_id' => $orderId]);
            throw new \InvalidArgumentException('Order cannot be tracked');
        }

        return $order->load('deliveryAddress', 'distributionCenter', 'deliveryPerson');
    }

    private function isTrackingCompleted(?DeliveryTracking $tracking): bool
    {
        return $tracking !== null && $tracking->status->equals(DeliveryTrackingStatus::DELIVERED());
    }

    private function extractCoordinatesData(StartDeliveryTrackingRequest $request): array
    {
        $validated = $request->validated();

        return [
            'lat' => (float) $validated['driver_lat'],
            'lng' => (float) $validated['driver_lng'],
        ];
    }

    private function ensureTrackingExists(
        ?DeliveryTracking $existingTracking,
        Order $order,
        array $coordinates
    ): DeliveryTracking {
        if ($existingTracking) {
            return $existingTracking;
        }

        Log::info('Creating new tracking', ['order_id' => $order->id]);

        $totalDistance = $this->calculateInitialDistance($order);

        return $this->deliveryTrackingRepository->create([
            'order_id' => $order->id,
            'status' => DeliveryTrackingStatus::PENDING(),
            'total_distance' => $totalDistance,
            'distance_remaining' => $totalDistance,
        ]);
    }

    private function startTrackingProcess(
        DeliveryTracking $tracking,
        array $coordinates,
        Order $order
    ): void {
        $routeData = $this->calculateDeliveryRoute($order, $coordinates);

        $tracking->update([
            'status' => DeliveryTrackingStatus::STARTED(),
            'driver_lat' => $coordinates['lat'],
            'driver_lng' => $coordinates['lng'],
            'estimated_duration' => $routeData->duration, // En secondes (Google Maps API)
            'distance_remaining' => $routeData->distance,
            'route_geometry' => $routeData->geometry,
            'started_at' => now(),
        ]);
    }

    private function calculateInitialDistance(Order $order): float
    {
        if (! $this->hasValidCoordinates($order)) {
            return 0.0;
        }

        $startLat = (float) $order->distributionCenter->latitude;
        $startLng = (float) $order->distributionCenter->longitude;
        $endLat = (float) $order->deliveryAddress->latitude;
        $endLng = (float) $order->deliveryAddress->longitude;

        return $this->calculateHaversineDistance($startLat, $startLng, $endLat, $endLng);
    }

    private function hasValidCoordinates(Order $order): bool
    {
        return $order->distributionCenter?->latitude !== null
            && $order->distributionCenter?->longitude !== null
            && $order->deliveryAddress?->latitude !== null
            && $order->deliveryAddress?->longitude !== null;
    }

    private function calculateHaversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    private function calculateDeliveryRoute(Order $order, array $coordinates): object
    {
        if ($this->hasInvalidDestinationCoordinates($order)) {
            // If the delivery address uses a location link instead of GPS, return empty route data
            if ($order->deliveryAddress?->hasLocationLink()) {
                Log::info('Delivery address uses location link, skipping route calculation', ['order_id' => $order->id]);

                return (object) [
                    'duration' => null,
                    'distance' => 0,
                    'geometry' => null,
                ];
            }

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

    private function updateOrderStatus(Order $order): void
    {
        $updateDto = new UpdateOrderDTO(status: OrderStatus::PROCESSING());
        $this->orderService->update($order, $updateDto->toArrayFiltered());
    }

    private function broadcastDeliveryUpdate(DeliveryTracking $tracking): void
    {
        broadcast(new DeliveryPositionUpdated($tracking));
    }

    private function createConflictResponse(): ApiResponse
    {
        return DeliveryTrackingResponse::error(
            'Delivery tracking already completed for this order.',
            null,
            Response::HTTP_CONFLICT
        );
    }

    private function createSuccessResponse(DeliveryTracking $tracking, bool $wasRestarted): ApiResponse
    {
        $message = $wasRestarted ? 'Delivery restarted successfully.' : 'Delivery started successfully.';
        $freshTracking = $tracking->fresh()->load('order.customer', 'order.deliveryAddress');

        return DeliveryTrackingResponse::make($freshTracking, $message);
    }

    private function validateBottlesLinked(Order $order): void
    {
        if (! $order->hasBottleItems()) {
            return;
        }

        if (! $order->areAllBottlesScanned()) {
            Log::warning('Delivery start blocked: bottles not linked', [
                'order_id' => $order->id,
                'progress' => $order->bottle_scan_progress.'%',
            ]);

            throw new \InvalidArgumentException(
                'Impossible de démarrer la livraison. Les bouteilles de gaz n\'ont pas encore été liées à cette commande.'
            );
        }
    }

    private function validateDeliveryPersonAccess(Order $order): void
    {
        $authenticatedUser = auth()->user();

        if (! $order->deliveryPerson || $order->deliveryPerson->user_id !== $authenticatedUser->id) {
            Log::warning('Unauthorized delivery person access attempt', [
                'order_id' => $order->id,
                'authenticated_user_id' => $authenticatedUser->id,
                'assigned_delivery_person_id' => $order->deliveryPerson?->user_id,
            ]);

            throw new \InvalidArgumentException('You are not authorized to access this delivery');
        }
    }
}
