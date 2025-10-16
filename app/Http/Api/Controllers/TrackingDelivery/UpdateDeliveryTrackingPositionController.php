<?php

namespace App\Http\Api\Controllers\TrackingDelivery;

use App\Contracts\DeliveryTrackingServiceInterface;
use App\Http\Api\Requests\TrackingDelivery\UpdateDeliveryTrackingPositionRequest;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Api\Responses\TrackingDelivery\DeliveryTrackingResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Log;

class UpdateDeliveryTrackingPositionController extends Controller
{
    public function __construct(
        private readonly DeliveryTrackingServiceInterface $deliveryTrackingService,
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Update delivery tracking position.
     *
     * Route: PUT /api/delivery-tracking/{orderId}/position
     * Name: api.delivery-tracking.position.update
     */
    public function __invoke(UpdateDeliveryTrackingPositionRequest $request, int $orderId): ApiResponse
    {
        $this->validateDeliveryPersonAccess($orderId);

        $updatedTracking = $this->deliveryTrackingService->updatePosition($orderId, $request);

        return DeliveryTrackingResponse::make(
            $updatedTracking,
            'Delivery position updated successfully.'
        );
    }

    private function validateDeliveryPersonAccess(int $orderId): void
    {
        /** @var Order|null $order */
        $order = $this->orderRepository->find($orderId);

        if (! $order) {
            Log::warning('Order not found for position update', ['order_id' => $orderId]);
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
