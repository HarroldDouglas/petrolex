<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\DTOs\RouteDTO;
use App\Enums\DeliveryTrackingStatus;
use App\Events\DeliveryPositionUpdated;
use App\Events\DeliveryStatusUpdated;
use App\Http\Api\Requests\TrackingDelivery\UpdateDeliveryTrackingPositionRequest;
use App\Models\DeliveryTracking;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Services\Shared\Cache\CacheServiceInterface;
use App\Contracts\RouteCalculatorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class DeliveryTrackingService implements DeliveryTrackingServiceInterface
{
    private const CACHE_DURATION = 3600;
    private const NEAR_DESTINATION_THRESHOLD = 95.0;

    public function __construct(
        private readonly DeliveryTrackingRepositoryInterface $repository,
        private readonly CacheServiceInterface $cacheService,
        private readonly RouteCalculatorInterface $mapboxService
    ) {}

    public function calculateRoute(float $fromLng, float $fromLat, float $toLng, float $toLat): RouteDTO
    {
        return $this->mapboxService->calculateRoute($fromLng, $fromLat, $toLng, $toLat);
    }

    public function updatePosition(int $orderId, UpdateDeliveryTrackingPositionRequest $request): DeliveryTracking
    {
        Log::info('Updating delivery tracking position', ['order_id' => $orderId]);

        return DB::transaction(function () use ($request, $orderId): DeliveryTracking {
            $this->validateDeliveryNotCompleted($orderId);

            $deliveryTracking = $this->getValidatedDeliveryTracking($orderId);
            $positionData = $this->extractPositionData($request);

            $previousStatus = $deliveryTracking->status->value;
            $newStatus = $this->determineNewStatus($deliveryTracking, $positionData);

            $updatedTracking = $this->updateTrackingPosition(
                $deliveryTracking,
                $positionData,
                $newStatus
            );

            $this->updateCache($orderId, $positionData, $newStatus);
            $this->dispatchEvents($updatedTracking, $previousStatus, $newStatus->value);

            Log::info('Delivery tracking position updated successfully', ['order_id' => $orderId]);

            return $updatedTracking;
        });
    }

    private function validateDeliveryNotCompleted(int $orderId): void
    {
        $cacheKey = "tracking_data_{$orderId}";
        $cachedData = $this->cacheService->get($cacheKey);

        if ($cachedData && isset($cachedData['status'])) {
            $status = DeliveryTrackingStatus::from($cachedData['status']);
            if ($status->isCompleted()) {
                Log::warning('Cannot update position for completed delivery', ['order_id' => $orderId]);
                throw new InvalidArgumentException('Cannot update position for completed delivery.');
            }
        }
    }

    private function getValidatedDeliveryTracking(int $orderId): DeliveryTracking
    {
        $deliveryTracking = $this->repository->findByOrder($orderId);

        if (!$deliveryTracking) {
            Log::warning('Delivery tracking not found', ['order_id' => $orderId]);
            throw new InvalidArgumentException('Delivery tracking not found.');
        }

        if ($deliveryTracking->status->isCompleted()) {
            Log::warning('Cannot update position for completed delivery', ['order_id' => $orderId]);
            throw new InvalidArgumentException('Cannot update position for completed delivery.');
        }

        return $deliveryTracking;
    }

    private function extractPositionData(UpdateDeliveryTrackingPositionRequest $request): array
    {
        $validated = $request->validated();

        return [
            'lat' => (float) $validated['driver_lat'],
            'lng' => (float) $validated['driver_lng'],
            'speed' => (float) ($validated['current_speed'] ?? 0),
            'progress_percentage' => isset($validated['progress_percentage'])
                ? (float) $validated['progress_percentage']
                : null,
            'distance_remaining' => isset($validated['distance_remaining'])
                ? (float) $validated['distance_remaining']
                : null,
            'estimated_duration' => isset($validated['estimated_duration'])
                ? (int) $validated['estimated_duration']
                : null,
        ];
    }

    private function determineNewStatus(DeliveryTracking $tracking, array $positionData): DeliveryTrackingStatus
    {
        $currentStatus = $tracking->status;
        $progressPercentage = $positionData['progress_percentage'];

        if ($progressPercentage !== null && $progressPercentage >= self::NEAR_DESTINATION_THRESHOLD) {
            return DeliveryTrackingStatus::IN_PROGRESS();
        }

        if ($currentStatus->value === DeliveryTrackingStatus::PENDING()->value) {
            return DeliveryTrackingStatus::IN_PROGRESS();
        }

        return $currentStatus;
    }

    private function updateTrackingPosition(
        DeliveryTracking $tracking,
        array $positionData,
        DeliveryTrackingStatus $newStatus
    ): DeliveryTracking {
        $updateData = [
            'driver_lat' => $positionData['lat'],
            'driver_lng' => $positionData['lng'],
            'current_speed' => $positionData['speed'],
            'status' => $newStatus,
            'updated_at' => now(),
        ];

        if ($positionData['progress_percentage'] !== null) {
            $updateData['progress_percentage'] = $positionData['progress_percentage'];
        }

        if ($positionData['distance_remaining'] !== null) {
            $updateData['distance_remaining'] = $positionData['distance_remaining'];
        }

        if ($positionData['estimated_duration'] !== null) {
            $updateData['estimated_duration'] = $positionData['estimated_duration'];
        }

        Log::info('Updating tracking with calculated data from driver', [
            'order_id' => $tracking->order_id,
            'progress' => $positionData['progress_percentage'],
            'distance_remaining' => $positionData['distance_remaining'],
            'estimated_duration' => $positionData['estimated_duration'],
        ]);

        return $this->repository->update($tracking, $updateData);
    }

    private function updateCache(int $orderId, array $positionData, DeliveryTrackingStatus $status): void
    {
        $cacheKey = "tracking_data_{$orderId}";

        $cacheData = [
            'status' => $status->value,
            'position' => [
                'lat' => $positionData['lat'],
                'lng' => $positionData['lng'],
            ],
            'progress_percentage' => $positionData['progress_percentage'],
            'distance_remaining' => $positionData['distance_remaining'],
            'estimated_duration' => $positionData['estimated_duration'],
            'last_update' => now()->toISOString(),
        ];

        $this->cacheService->put($cacheKey, $cacheData, self::CACHE_DURATION);
    }

    private function dispatchEvents(
        DeliveryTracking $tracking,
        string $previousStatus,
        string $newStatus
    ): void {
        DeliveryPositionUpdated::dispatch($tracking);

        if ($previousStatus !== $newStatus) {
            DeliveryStatusUpdated::dispatch($tracking, $previousStatus);
        }
    }
}