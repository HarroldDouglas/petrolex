<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGateway;
use App\Models\OrderPayment;
use App\DTOs\PaymentResponse;
use App\DTOs\PaymentCallbackData;
use App\Enums\PaymentStatus;

class CreditCardGateway implements PaymentGateway
{
    public function initiatePayment(OrderPayment $payment): PaymentResponse
    {
        // Logique d'intégration avec une API de carte bancaire (ex: Stripe, PayGate)
        return new PaymentResponse(
            success: true,
            status: PaymentStatus::PENDING()->value,
            transactionReference: 'CC_REF_'.uniqid(),
            paymentUrl: 'https://example.com/credit-card-payment/'.uniqid()
        );
    }

    public function handleCallback(PaymentCallbackData $callbackData): PaymentResponse
    {
        // Traitement du callback Carte Bancaire
        return new PaymentResponse(
            success: true,
            status: $callbackData->status,
            transactionReference: $callbackData->transactionReference
        );
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        // Vérification du statut de paiement auprès du prestataire de carte bancaire
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
