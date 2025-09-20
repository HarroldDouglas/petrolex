<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Order;

use App\Http\Api\Resources\Order\OrderResource;
use App\Http\Api\Responses\ApiResponse;
use App\Models\Order;
use App\Models\OrderPayment;

final class CreateOrderResponse extends ApiResponse
{
    public static function withOrderAndPayment(Order $order, OrderPayment $payment): self
    {
        $data = [
            'order' => new OrderResource($order),
            'payment' => [
                'payment_reference' => $payment->payment_reference,
                'payment_status' => $payment->payment_status->value,
                'payment_status_label' => $payment->payment_status->label ?? $payment->payment_status->value,
                'payment_method' => $payment->payment_method->value,
                'payment_method_label' => $payment->payment_method->label ?? $payment->payment_method->value,
                'amount_due' => $payment->amount_due,
                'amount_paid' => $payment->amount_paid,
            ],
        ];

        return new self(
            $data,
            'Commande créée avec succès. Procédez au paiement.',
            true,
            201
        );
    }
}
