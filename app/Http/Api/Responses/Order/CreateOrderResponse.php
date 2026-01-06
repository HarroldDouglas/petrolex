<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderDetailResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;
use App\Models\OrderPayment;

final class CreateOrderResponse extends ApiResponse
{
    public static function withOrder(Order $order): self
    {
        // wallet_amount_used is now from DB, wallet_balance_before and total_amount_to_pay are temporary attributes
        $walletBalanceBefore = $order->getAttribute('wallet_balance_before') ?? 0;
        $walletAmountUsed = (float) $order->wallet_amount_used;
        $totalAmountToPay = $order->getAttribute('total_amount_to_pay') ?? ((float) $order->total_amount - $walletAmountUsed);

        $data = [
            'order' => new OrderDetailResource($order),
            'wallet_info' => [
                'wallet_balance_before' => $walletBalanceBefore,
                'wallet_amount_used' => $walletAmountUsed,
                'wallet_balance_after' => $walletBalanceBefore - $walletAmountUsed,
                'wallet_transaction_reference' => $order->getAttribute('wallet_transaction_reference'),
            ],
            'payment_info' => [
                'total_amount' => (float) $order->total_amount,
                'total_amount_to_pay' => $totalAmountToPay,
                'payment_required' => $totalAmountToPay > 0,
            ],
        ];

        return new self(
            $data,
            __('api.order_created_success'),
            true,
            201
        );
    }

    public static function withOrderAndPayment(Order $order, OrderPayment $payment): self
    {
        $data = [
            'order' => new OrderDetailResource($order),
            'payment' => [
                'payment_reference' => $payment->payment_reference,
                'payment_status' => $payment->payment_status->value,
                'payment_status_label' => $payment->payment_status->label ?? $payment->payment_status->value,
                'payment_method' => $payment->payment_method->value,
                'payment_method_label' => $payment->payment_method->label ?? $payment->payment_method->value,
                'payment_method_validation_text' => $payment->payment_method->validationText(),
                'amount_due' => $payment->amount_due,
                'amount_paid' => $payment->amount_paid,
            ],
        ];

        return new self(
            $data,
            __('api.order_created_success'),
            true,
            201
        );
    }
}
