<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Enums\DeliveryTrackingStatus;
use App\Http\Api\Requests\TrackingDelivery\CreateDeliveryTrackingRequest;
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
    public function __invoke(CreateDeliveryTrackingRequest $request): DeliveryTrackingResponse
    {
        $orderId = filter_var($request->validated('order_id'), FILTER_VALIDATE_INT);

        if ($orderId === false) {
            return DeliveryTrackingResponse::error('Invalid order ID.', Response::HTTP_BAD_REQUEST);
        }

        /** @var \App\Models\Order|null $order */
        $order = $this->orderRepository->find($orderId);

        if (! $order) {
            return DeliveryTrackingResponse::error('Order not found.', Response::HTTP_NOT_FOUND);
        }

        if (! $order->canBeTracked()) {
            return DeliveryTrackingResponse::error('Order cannot be tracked.', Response::HTTP_BAD_REQUEST);
        }

        $deliveryTracking = $this->deliveryTrackingRepository->create([
            'order_id' => $order->id,
            'status' => DeliveryTrackingStatus::PENDING(),
        ]);

        return DeliveryTrackingResponse::make(
            $deliveryTracking->load('order.customer', 'order.deliveryAddress'),
            'Delivery tracking created successfully.',
            Response::HTTP_CREATED
        );
    }
}
