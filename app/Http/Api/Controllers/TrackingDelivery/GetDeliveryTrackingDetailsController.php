<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class GetDeliveryTrackingDetailsController extends Controller
{
    public function __construct(private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository) {}

    /**
     * Get a delivery's details.
     *
     * Route: GET /api/tracking/delivery/{orderNumber}
     * Name: tracking.delivery.details
     */
    public function __invoke(string $orderNumber): ApiResponse
    {
        $deliveryTracking = $this->deliveryTrackingRepository->findByOrderNumber($orderNumber);

        if (! $deliveryTracking) {
            return DeliveryTrackingResponse::error('Delivery tracking not found.', Response::HTTP_NOT_FOUND);
        }

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery tracking details retrieved successfully.'
        );
    }
}
