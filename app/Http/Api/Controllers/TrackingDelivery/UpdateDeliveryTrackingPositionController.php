<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\Enums\DeliveryTrackingStatus;
use App\Events\DeliveryPositionUpdated;
use App\Events\DeliveryStatusUpdated;
use App\Http\Api\Requests\TrackingDelivery\UpdateDeliveryTrackingPositionRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Services\Shared\Cache\CacheServiceInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class UpdateDeliveryTrackingPositionController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly CacheServiceInterface $cacheService
    ) {}

    /**
     * Update delivery tracking position.
     *
     * Route: PATCH /api/tracking/delivery/{orderId}/position
     * Name: tracking.delivery.position.update
     */
    public function __invoke(UpdateDeliveryTrackingPositionRequest $request, int $orderId): ApiResponse
    {
        try {
            Log::info('Attempting to update delivery tracking position for order ID: '.$orderId);

            // Vérifier d'abord le cache pour éviter les mises à jour inutiles
            $cacheKey = "tracking_data_{$orderId}";
            $cachedData = $this->cacheService->get($cacheKey);
            if ($cachedData && in_array($cachedData['status'], ['delivered', 'cancelled'])) {
                Log::warning('Cannot update position for completed delivery. Order ID: '.$orderId);

                return DeliveryTrackingResponse::error('Cannot update position for completed delivery.', null, Response::HTTP_CONFLICT);
            }

            $deliveryTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

            if (! $deliveryTracking) {
                Log::warning('Delivery tracking not found for order ID: '.$orderId);

                return DeliveryTrackingResponse::error('Delivery tracking not found.', null, Response::HTTP_NOT_FOUND);
            }

            // Vérifier si la livraison est terminée
            if ($deliveryTracking->status->value === DeliveryTrackingStatus::DELIVERED()->value) {
                Log::warning('Cannot update position for completed delivery. Order ID: '.$orderId);

                return DeliveryTrackingResponse::error('Cannot update position for completed delivery.', null, Response::HTTP_CONFLICT);
            }

            $validated = $request->validated();
            $driverLat = (float) $validated['driver_lat'];
            $driverLng = (float) $validated['driver_lng'];
            $currentSpeed = $validated['current_speed'] ?? 0;

            // NOUVEAU : Utiliser les données calculées côté livreur
            $progressPercentage = $validated['progress_percentage'] ?? null;
            $distanceRemaining = $validated['distance_remaining'] ?? null;
            $estimatedDuration = $validated['estimated_duration'] ?? null;

            // Déterminer le statut basé sur la progression
            $newStatus = $deliveryTracking->status;
            if ($progressPercentage !== null && $progressPercentage >= 95) {
                // Très proche de la destination, garder IN_PROGRESS jusqu'à confirmation manuelle
                $newStatus = DeliveryTrackingStatus::IN_PROGRESS();
            } elseif ($deliveryTracking->status->value === DeliveryTrackingStatus::PENDING()->value) {
                // Premier update, passer à IN_PROGRESS
                $newStatus = DeliveryTrackingStatus::IN_PROGRESS();
            }

            // Capturer le statut précédent pour l'événement
            $previousStatus = $deliveryTracking->status->value;

            // Préparer les données de mise à jour avec les valeurs calculées côté livreur
            $updateData = [
                'driver_lat' => $driverLat,
                'driver_lng' => $driverLng,
                'current_speed' => $currentSpeed,
                'status' => $newStatus,
                'updated_at' => now(),
            ];

            // Ajouter les données calculées si elles sont présentes
            if ($progressPercentage !== null) {
                $updateData['progress_percentage'] = $progressPercentage;
            }
            if ($distanceRemaining !== null) {
                $updateData['distance_remaining'] = $distanceRemaining;
            }
            if ($estimatedDuration !== null) {
                $updateData['estimated_duration'] = $estimatedDuration;
            }

            Log::info('Updating tracking with calculated data from driver', [
                'order_id' => $orderId,
                'progress' => $progressPercentage,
                'distance_remaining' => $distanceRemaining,
                'estimated_duration' => $estimatedDuration,
            ]);

            // Mettre à jour avec les données centralisées
            $updatedTracking = $this->deliveryTrackingRepository->update(
                $deliveryTracking,
                $updateData
            );

            // Mettre à jour le cache
            $this->cacheService->put($cacheKey, [
                'status' => $newStatus->value,
                'position' => [
                    'lat' => $driverLat,
                    'lng' => $driverLng,
                ],
                'progress_percentage' => $progressPercentage,
                'distance_remaining' => $distanceRemaining,
                'estimated_duration' => $estimatedDuration,
                'last_update' => now()->toISOString(),
            ], 3600);

            // Déclencher les événements appropriés
            DeliveryPositionUpdated::dispatch($updatedTracking);

            if ($previousStatus !== $newStatus->value) {
                DeliveryStatusUpdated::dispatch($updatedTracking, $previousStatus, $newStatus->value);
            }

            Log::info('Delivery tracking position updated successfully for order ID: '.$orderId);

            return DeliveryTrackingResponse::make(
                $updatedTracking,
                'Delivery position updated successfully.'
            );

        } catch (\Exception $e) {
            Log::error('Error updating delivery tracking position for order ID: '.$orderId.'. Error: '.$e->getMessage());

            return DeliveryTrackingResponse::error('Failed to update delivery position.', null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
