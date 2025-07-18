<?php

namespace App\Http\Api\Controllers\Payment;

use App\Enums\PaymentMethod;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;

class GetPaymentMethodsController extends Controller
{
    /**
     * Get all available payment methods.
     *
     * Route: GET /payment-methods
     */
    public function __invoke(): ApiResponse
    {
        $paymentMethods = collect(PaymentMethod::cases())
            ->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label,
            ])->toArray();

        return ApiResponse::success(
            data: $paymentMethods,
            message: 'Payment methods retrieved successfully.'
        );
    }
}
