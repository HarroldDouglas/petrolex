<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Payment;

use App\Enums\PaymentMethod;
use App\Http\Api\Requests\Payment\InitiatePaymentRequest;
use App\Http\Api\Responses\Order\OrderDetailsResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class InitiatePaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function __invoke(InitiatePaymentRequest $request, Order $order): OrderDetailsResponse
    {
        if ($order->customer_id !== $request->user()->customer->id) {
            throw new BadRequestHttpException(__('api.order_not_belongs_to_you'));
        }

        if (! $order->canAcceptPayment()) {
            throw new BadRequestHttpException(__('api.order_cannot_accept_payment'));
        }

        $data = $request->validated();
        $useWallet = $data['use_wallet'] ?? true;

        // Wallet-only payment (no external payment method provided)
        if (empty($data['payment_method'])) {
            $result = $this->paymentService->initiatePaymentWithWallet(
                $order,
                PaymentMethod::WALLET(), // Use wallet as the method
                [],
                $useWallet
            );

            $order->loadDetailRelations();

            return OrderDetailsResponse::withOrder(
                $order,
                __('api.payment_wallet_success')
            )->with('payment_breakdown', $result['payment_breakdown']);
        }

        // Payment with external method (optionally using wallet for partial amount)
        $result = $this->paymentService->initiatePaymentWithWallet(
            $order,
            PaymentMethod::from($data['payment_method']),
            $data['payment_details'] ?? [],
            $useWallet
        );

        $order->loadDetailRelations();

        $message = $result['external_payment_required']
            ? __('api.payment_initiated_success')
            : __('api.payment_wallet_success');

        return OrderDetailsResponse::withOrder($order, $message)
            ->with('payment_breakdown', $result['payment_breakdown']);
    }
}
