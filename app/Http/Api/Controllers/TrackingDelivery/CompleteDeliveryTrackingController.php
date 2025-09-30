<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Enums\DeliveryTrackingStatus;
use App\Enums\OrderStatus;
use App\Events\DeliveryStatusUpdated;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class CompleteDeliveryTrackingController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderService $orderService,
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Complete a delivery.
     *
     * Route: PATCH /api/tracking/delivery/{orderId}/complete
     * Name: tracking.delivery.complete
     */
    public function __invoke(int $orderId): ApiResponse
    {
        Log::info('Attempting to complete delivery tracking for order ID: '.$orderId);

        $this->validateDeliveryPersonAccess($orderId);
        $deliveryTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

        if (! $deliveryTracking) {
            Log::warning('Delivery tracking not found for order ID: '.$orderId);

            return DeliveryTrackingResponse::error('Delivery tracking not found.', null, Response::HTTP_NOT_FOUND);
        }

        if ($deliveryTracking->status->value === DeliveryTrackingStatus::DELIVERED()->value) {
            Log::info('Delivery tracking for order ID '.$orderId.' is already completed.');

            return DeliveryTrackingResponse::error('Delivery tracking is already completed.', null, Response::HTTP_CONFLICT);
        }

        $previousStatus = $deliveryTracking->status->value;
        $deliveryTracking = $this->deliveryTrackingRepository->update(
            $deliveryTracking,
            [
                'status' => DeliveryTrackingStatus::DELIVERED(),
                'delivered_at' => now(),
            ]
        );

        $order = $deliveryTracking->order;
        if ($order) {
            $this->orderService->updateOrderStatus($order, OrderStatus::DELIVERED());
            Log::info('Order status updated to DELIVERED for order ID: '.$orderId);
        }

        broadcast(new DeliveryStatusUpdated($deliveryTracking->fresh(), $previousStatus));

        Log::info('Delivery tracking completed successfully for order ID: '.$orderId);

        return DeliveryTrackingResponse::make(
            $deliveryTracking,
            'Delivery completed successfully.'
        );
    }

    private function validateDeliveryPersonAccess(int $orderId): void
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->find($orderId);

        if (! $order) {
            Log::warning('Order not found for complete delivery', ['order_id' => $orderId]);
            abort(404, 'Order not found');
        }

        $order->load('deliveryPerson');
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
