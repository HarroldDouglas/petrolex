<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Payment;

use App\Http\Api\Requests\Payment\PaymentCallbackRequest;
use App\Http\Api\Responses\Payment\PaymentCallbackResponse;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;

final class PaymentCallbackController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Handle payment gateway callback.
     *
     * Route: POST /api/payments/callback
     * Name: api.payments.callback
     */
    public function __invoke(PaymentCallbackRequest $request): PaymentCallbackResponse
    {
        $validated = $request->validated();

        // Log the callback for debugging/auditing
        Log::info('Payment callback received', [
            'order_id' => $validated['app_transaction_ref'],
            'transaction_status' => $validated['transaction_status'],
            'transaction_amount' => $validated['transaction_amount'],
            'operator_transaction_ref' => $validated['operator_transaction_ref'],
        ]);

        try {
            // Process the callback through PaymentService
            $this->paymentService->handleCallback(
                $validated['app_transaction_ref'], // This is the order ID
                $validated
            );

            return PaymentCallbackResponse::paymentSuccess(
                $validated['app_transaction_ref'],
                $validated['transaction_status']
            );

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Payment callback processing failed', [
                'order_id' => $validated['app_transaction_ref'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return PaymentCallbackResponse::paymentError(
                $validated['app_transaction_ref'],
                $e->getMessage()
            );
        }
    }
}
