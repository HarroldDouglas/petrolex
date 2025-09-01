<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class GetDeliveryTrackingDetailsController extends Controller
{
    public function __construct(private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository) {}

    /**
     * Get a delivery's details.
     *
     * Route: GET /api/tracking/delivery/{orderId}
     * Name: tracking.delivery.details
     */
    public function __invoke(int $orderId): ApiResponse
    {
        Log::info('Attempting to get delivery tracking details for order ID: '.$orderId);
        $deliveryTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

        if (! $deliveryTracking) {
            Log::warning('Delivery tracking not found for order ID: '.$orderId);

            return DeliveryTrackingResponse::error('Delivery tracking not found.', Response::HTTP_NOT_FOUND);
        }

        $deliveryTracking->load([
            'order.customer',
            'order.deliveryAddress',
            'order.deliveryPerson',
        ]);

        Log::info('Delivery tracking details retrieved successfully for order ID: '.$orderId);

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery tracking details retrieved successfully.'
        );
    }
}
