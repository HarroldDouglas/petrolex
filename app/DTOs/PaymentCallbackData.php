<?php

namespace App\DTOs;

readonly class PaymentCallbackData
{
    public function __construct(
        public string $transactionReference,
        public string $status,
        public ?float $amount,
        public array $rawData
    ) {}
}
