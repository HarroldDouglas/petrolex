<?php

declare(strict_types=1);

namespace App\DTOs;

final class PaymentDetailsData
{
    public function __construct(
        public readonly ?string $phone = null,
        public readonly ?string $cardNumber = null,
        public readonly ?string $cvv = null,
        public readonly ?string $expiryDate = null,
        public readonly ?string $cardholderName = null,
    ) {}

    /**
     * Create from array data
     */
    public static function from(array $data): self
    {
        return new self(
            phone: $data['phone'] ?? null,
            cardNumber: $data['card_number'] ?? null,
            cvv: $data['cvv'] ?? null,
            expiryDate: $data['expiry_date'] ?? null,
            cardholderName: $data['cardholder_name'] ?? null,
        );
    }

    /**
     * Check if this contains mobile payment details
     */
    public function isMobilePayment(): bool
    {
        return ! empty($this->phone);
    }

    /**
     * Check if this contains card payment details
     */
    public function isCardPayment(): bool
    {
        return ! empty($this->cardNumber) &&
               ! empty($this->cvv) &&
               ! empty($this->expiryDate) &&
               ! empty($this->cardholderName);
    }

    /**
     * Get the phone number (for mobile payments)
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Get card details as array (for card payments)
     */
    public function getCardDetails(): array
    {
        return [
            'card_number' => $this->cardNumber,
            'cvv' => $this->cvv,
            'expiry_date' => $this->expiryDate,
            'cardholder_name' => $this->cardholderName,
        ];
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'phone' => $this->phone,
            'card_number' => $this->cardNumber,
            'cvv' => $this->cvv,
            'expiry_date' => $this->expiryDate,
            'cardholder_name' => $this->cardholderName,
        ]);
    }
}
