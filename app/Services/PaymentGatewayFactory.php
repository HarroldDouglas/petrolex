<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Services\PaymentGateways\CreditCardGateway;
use App\Services\PaymentGateways\MTNMoneyGateway;
use App\Services\PaymentGateways\OrangeMoneyGateway;

class PaymentGatewayFactory
{
    private array $gateways = [];

    public function __construct()
    {
        $this->gateways = [
            new OrangeMoneyGateway,
            new MTNMoneyGateway,
            new CreditCardGateway,
        ];
    }

    public function create(string $paymentMethod): PaymentGateway
    {
        foreach ($this->gateways as $gateway) {
            if ($gateway->supports($paymentMethod)) {
                return $gateway;
            }
        }

        throw new \InvalidArgumentException('Unsupported payment method: {$paymentMethod}');
    }
}
