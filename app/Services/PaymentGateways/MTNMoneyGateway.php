<?php

namespace App
Services\PaymentGateways;

use App\Contracts\PaymentGateway;
use App\Models\OrderPayment;
use App\DTOs\PaymentResponse;
use App\DTOs\PaymentCallbackData;
use App\Enums\PaymentStatus;

class MTNMoneyGateway implements PaymentGateway
{
    public function initiatePayment(OrderPayment $payment): PaymentResponse
    {
        // Logique d'intégration avec l'API MTN Money
        return new PaymentResponse(
            success: true,
            status: PaymentStatus::PENDING()->value,
            transactionReference: 'MTN_REF_'.uniqid(),
            paymentUrl: 'https://example.com/mtn-money-payment/'.uniqid()
        );
    }

    public function handleCallback(PaymentCallbackData $callbackData): PaymentResponse
    {
        // Traitement du callback MTN Money
        return new PaymentResponse(
            success: true,
            status: $callbackData->status,
            transactionReference: $callbackData->transactionReference
        );
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        // Vérification du statut de paiement auprès de MTN Money
        return new PaymentResponse(
            success: true,
            status: PaymentStatus::PAID()->value,
            transactionReference: $transactionReference
        );
    }

    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'mtn_money';
    }
}
