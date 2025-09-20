<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\CreateOrderDTO;
use App\Http\Api\Requests\Order\CreateOrderRequest;
use App\Http\Api\Responses\Order\CreateOrderResponse;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;

final class CreateOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PaymentService $paymentService
    ) {}

    public function __invoke(CreateOrderRequest $request): CreateOrderResponse
    {
        $data = $request->validated();
        $data['customer_id'] = $request->user()->customer->id;

        $orderDTO = CreateOrderDTO::from($data);

        return DB::transaction(function () use ($orderDTO) {
            $order = $this->orderService->create($orderDTO->toArray());

            $payment = $this->paymentService->initiatePayment(
                $order,
                $orderDTO->payment_method
            );

            $order->load([
                'items.productCategory',
                'deliveryAddress.neighborhood.municipality.city.country',
                'customer.user.country',
                'customer.deliveryAddresses.neighborhood.municipality.city.country',
                'distributionCenter.neighborhood.municipality.city.country',
            ]);

            return CreateOrderResponse::withOrderAndPayment($order, $payment);
        });
    }
}
