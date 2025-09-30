<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\ScanEmptyBottleDTO;
use App\Http\Api\Responses\Order\ScanEmptyBottleResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ScanEmptyBottleRequest;
use App\Models\Order;
use App\Services\Order\OrderService;

final class ScanEmptyBottleController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Handle the incoming request.
     *
     * Route: POST /api/orders/{order}/scan-empty-bottle
     * Name: api.orders.scan-empty-bottle
     */
    public function __invoke(ScanEmptyBottleRequest $request, Order $order): ScanEmptyBottleResponse
    {
        $user = auth()->user();

        $isOrderOwner = $user->customer && $user->customer->id === $order->customer_id;
        $isDeliveryPerson = $user->hasRole('delivery_person');

        if (! $isOrderOwner && ! $isDeliveryPerson) {
            abort(403, __('api.order_not_authorized_to_scan_bottles'));
        }

        $dto = new ScanEmptyBottleDTO(
            barcode: $request->validated('barcode'),
            orderItemId: (int) $request->validated('order_item_id'),
        );

        $success = $this->orderService->handleEmptyBottleReturn($dto, $order);

        // TODO uncomment this after mobile test
        /* if ($success) { */
        return ScanEmptyBottleResponse::success(null, __('api.empty_bottle_scanned_success'));
        /* } */

        return ScanEmptyBottleResponse::error(__('api.empty_bottle_scan_failed'));
    }
}
