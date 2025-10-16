<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\DeliveryTrackingRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class GetDeliveryTrackingDetailsController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingRepositoryInterface $deliveryTrackingRepository,
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Get a delivery's details.
     *
     * Route: GET /api/tracking/delivery/{orderId}
     * Name: tracking.delivery.details
     */
    public function __invoke(int $orderId): ApiResponse
    {
        Log::info('Attempting to get delivery tracking details for order ID: '.$orderId);

        $this->validateUserAccess($orderId);
        $deliveryTracking = $this->deliveryTrackingRepository->findByOrder($orderId);

        if (! $deliveryTracking) {
            Log::warning('Delivery tracking not found for order ID: '.$orderId);

            return DeliveryTrackingResponse::error('Delivery tracking not found.', null, Response::HTTP_NOT_FOUND);
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

    private function validateUserAccess(int $orderId): void
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->find($orderId);

        if (! $order) {
            Log::warning('Order not found for tracking details', ['order_id' => $orderId]);
            abort(404, 'Order not found');
        }

        $order->load('customer', 'deliveryPerson');
        $authenticatedUser = auth()->user();

        $isCustomer = $order->customer && $order->customer->user_id === $authenticatedUser->id;
        $isDeliveryPerson = $order->deliveryPerson && $order->deliveryPerson->user_id === $authenticatedUser->id;

        if (! $isCustomer && ! $isDeliveryPerson) {
            Log::warning('Unauthorized access attempt to tracking details', [
                'order_id' => $order->id,
                'authenticated_user_id' => $authenticatedUser->id,
                'customer_user_id' => $order->customer?->user_id,
                'delivery_person_user_id' => $order->deliveryPerson?->user_id,
            ]);

            throw new \InvalidArgumentException('You are not authorized to access this delivery');
        }
    }
}
