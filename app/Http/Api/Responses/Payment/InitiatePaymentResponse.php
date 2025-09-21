<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Payment;

use App\Http\Api\Resources\Order\OrderPaymentResource;
use App\Models\OrderPayment;
use Illuminate\Http\JsonResponse;

final class InitiatePaymentResponse extends JsonResponse
{
    public static function withPayment(OrderPayment $payment): self
    {
        return new self([
            'success' => true,
            'message' => __('payment.initiated_successfully'),
            'data' => [
                'payment' => new OrderPaymentResource($payment),
            ],
        ], 201);
    }
}
