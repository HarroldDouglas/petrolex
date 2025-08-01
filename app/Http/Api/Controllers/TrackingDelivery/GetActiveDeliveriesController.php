<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingCollectionResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;

final class GetActiveDeliveriesController extends Controller
{
    public function __construct(private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository) {}

    /**
     * Get all active deliveries.
     *
     * Route: GET /api/tracking/delivery/active
     * Name: tracking.delivery.active
     */
    public function __invoke(): DeliveryTrackingCollectionResponse
    {
        $deliveries = $this->deliveryTrackingRepository->getActives();

        return DeliveryTrackingCollectionResponse::make(
            $deliveries,
            'Active delivery trackings retrieved successfully.'
        );
    }
}
