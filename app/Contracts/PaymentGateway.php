<?php

namespace App\Contracts;

use App\Models\OrderPayment;
use App\DTOs\PaymentResponse;
use App\DTOs\PaymentCallbackData;

interface PaymentGateway
{
    public function initiatePayment(OrderPayment $payment): PaymentResponse;
    public function handleCallback(PaymentCallbackData $callbackData): PaymentResponse;
    public function verifyPayment(string $transactionReference): PaymentResponse;
    public function supports(string $paymentMethod): bool;
}
