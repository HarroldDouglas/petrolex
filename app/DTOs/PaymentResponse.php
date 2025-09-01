<?php

namespace App\DTOs;

readonly class PaymentResponse
{
    public function __construct(
        public bool $success,
        public string $status,
        public ?string $transactionReference = null,
        public ?string $paymentUrl = null,
        public ?string $errorMessage = null,
        public ?array $gatewayResponse = null
    ) {}
}
