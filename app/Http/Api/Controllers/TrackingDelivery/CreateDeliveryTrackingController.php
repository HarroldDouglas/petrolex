<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Enums\DeliveryTrackingStatus;
use App\Http\Api\Requests\TrackingDelivery\CreateDeliveryTrackingRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

final class CreateDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Create a new delivery tracking entry.
     *
     * Route: POST /api/tracking/delivery
     * Name: tracking.delivery.create
     */
    public function __invoke(CreateDeliveryTrackingRequest $request): ApiResponse
    {
        $orderId = filter_var($request->validated('order_id'), FILTER_VALIDATE_INT);

        if ($orderId === false) {
            return DeliveryTrackingResponse::error('Invalid order ID.', Response::HTTP_BAD_REQUEST);
        }

        $order = $this->orderRepository->find($orderId);

        if (! $order) {
            return DeliveryTrackingResponse::error('Order not found.', Response::HTTP_NOT_FOUND);
        }

        if (! $order->canBeTracked()) {
            return DeliveryTrackingResponse::error('Order cannot be tracked.', Response::HTTP_BAD_REQUEST);
        }

        $totalDistance = $this->calculateTotalDistance($order);

        $deliveryTracking = $this->deliveryTrackingRepository->create([
            'order_id' => $order->id,
            'status' => DeliveryTrackingStatus::PENDING(),
            'total_distance' => $totalDistance,
            'distance_remaining' => $totalDistance,
        ]);

        return DeliveryTrackingResponse::make(
            $deliveryTracking->load('order.customer', 'order.deliveryAddress'),
            'Delivery tracking created successfully.',
            Response::HTTP_CREATED
        );
    }

    private function calculateTotalDistance($order): float
    {
        if (! $order->distributionCenter || ! $order->deliveryAddress) {
            return 0.0;
        }

        $startLat = (float) $order->distributionCenter->latitude;
        $startLng = (float) $order->distributionCenter->longitude;
        $endLat = (float) $order->deliveryAddress->latitude;
        $endLng = (float) $order->deliveryAddress->longitude;

        $earthRadius = 6371;

        $dLat = deg2rad($endLat - $startLat);
        $dLng = deg2rad($endLng - $startLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($startLat)) * cos(deg2rad($endLat)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
