<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Enums\DeliveryTrackingStatus;
use App\Enums\OrderStatus;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class CompleteDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderService $orderService
    ) {}

    /**
     * Complete a delivery.
     *
     * Route: PATCH /api/tracking/delivery/{orderId}/complete
     * Name: tracking.delivery.complete
     */
    public function __invoke(int $orderId): ApiResponse
    {
        Log::info('Attempting to complete delivery tracking for order ID: ' . $orderId);

        $deliveryTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

        if (! $deliveryTracking) {
            Log::warning('Delivery tracking not found for order ID: ' . $orderId);
            return DeliveryTrackingResponse::error('Delivery tracking not found.', null, Response::HTTP_NOT_FOUND);
        }

        if ($deliveryTracking->status->value === DeliveryTrackingStatus::COMPLETED()->value) {
            Log::info('Delivery tracking for order ID ' . $orderId . ' is already completed.');
            return DeliveryTrackingResponse::error('Delivery tracking is already completed.', null, Response::HTTP_CONFLICT);
        }

        $deliveryTracking = $this->deliveryTrackingRepository->update(
            $deliveryTracking,
            [
                'status' => DeliveryTrackingStatus::COMPLETED(),
                'delivered_at' => now(),
            ]
        );

        $order = $deliveryTracking->order;
        if ($order) {
            $this->orderService->updateOrderStatus($order, OrderStatus::DELIVERED());
            Log::info('Order status updated to DELIVERED for order ID: ' . $orderId);
        }

        Log::info('Delivery tracking completed successfully for order ID: ' . $orderId);

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery completed successfully.'
        );
    }
}
