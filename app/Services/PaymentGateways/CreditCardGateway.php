<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGateway;
use App\DTOs\PaymentCallbackData;
use App\DTOs\PaymentDetailsData;
use App\DTOs\PaymentResponse;
use App\Enums\PaymentStatus;
use App\Models\OrderPayment;
use Illuminate\Support\Facades\Log;

class CreditCardGateway implements PaymentGateway
{
    public function initiatePayment(OrderPayment $payment, PaymentDetailsData $paymentDetails): PaymentResponse
    {
        $cardDetails = $paymentDetails->getCardDetails();

        Log::info('Credit Card Payment Initiated', [
            'payment_id' => $payment->id,
            'cardholder_name' => $cardDetails['cardholder_name'] ?? null,
            'card_ending' => $cardDetails['card_number'] ? '****'.substr($cardDetails['card_number'], -4) : null,
            'expiry_date' => $cardDetails['expiry_date'] ?? null,
        ]);

        return new PaymentResponse(
            success: true,
            status: PaymentStatus::PENDING()->value,
            transactionReference: 'CC_REF_'.uniqid(),
            paymentUrl: 'https://example.com/credit-card-payment/'.uniqid(),
            amount: $payment->amount_due,
            errorMessage: null,
            gatewayResponse: null
        );
    }

    public function handleCallback(PaymentCallbackData $callbackData): PaymentResponse
    {
       return new PaymentResponse(
            success: true,
            status: $callbackData->status,
            transactionReference: $callbackData->transactionReference
        );
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        return new PaymentResponse(
            success: true,
            status: PaymentStatus::PAID()->value,
            transactionReference: $transactionReference
        );
    }

    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'credit_card';
    }
}
