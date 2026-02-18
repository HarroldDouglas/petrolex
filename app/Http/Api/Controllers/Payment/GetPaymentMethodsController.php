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
        // Temporarily disabled payment methods (not yet ready for production)
        $disabledMethods = ['credit_card'];

        $paymentMethods = collect(PaymentMethod::cases())
            ->filter(fn ($case) => ! in_array($case->value, $disabledMethods))
            ->map(fn ($case) => [
                'value' => $case->value,
                'label' => $case->label,
                'validation_text' => $case->validationText(),
            ])
            ->values()
            ->toArray();

        return ApiResponse::success(
            data: $paymentMethods,
            message: 'Payment methods retrieved successfully.'
        );
    }
}
