<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGateway;
use App\DTOs\PaymentCallbackData;
use App\DTOs\PaymentDetailsData;
use App\DTOs\PaymentResponse;
use App\Enums\PaymentStatus;
use App\Models\OrderPayment;

class OrangeMoneyGateway implements PaymentGateway
{
    public function initiatePayment(OrderPayment $payment, PaymentDetailsData $paymentDetails): PaymentResponse
    {
        // Logique d'intégration avec l'API Orange Money
        // Pour l'instant, une implémentation de base

        $phoneNumber = $paymentDetails->getPhone() ?? $payment->order->customer->user->phone_number ?? null;

        return new PaymentResponse(
            success: true,
            status: PaymentStatus::PENDING()->value,
            transactionReference: 'OM_REF_'.uniqid(),
            paymentUrl: 'https://example.com/orange-money-payment/'.uniqid(),
            amount: $payment->amount_due,
            errorMessage: null,
            gatewayResponse: ['phone_used' => $phoneNumber]
        );
    }

    public function handleCallback(PaymentCallbackData $callbackData): PaymentResponse
    {
        // Traitement du callback Orange Money
        // Vérifier la signature, mettre à jour le statut, etc.
        return new PaymentResponse(
            success: true,
            status: $callbackData->status,
            transactionReference: $callbackData->transactionReference
        );
    }

    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        // Vérification du statut de paiement auprès d'Orange Money
        return new PaymentResponse(
            success: true,
            status: PaymentStatus::PAID()->value,
            transactionReference: $transactionReference
        );
    }

    public function supports(string $paymentMethod): bool
    {
        return $paymentMethod === 'orange_money';
    }
}
