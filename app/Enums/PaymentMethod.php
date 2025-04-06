<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CASH()
 * @method static self CREDIT_CARD()
 * @method static self BANK_TRANSFER()
 * @method static self MOBILE_MONEY()
 */
class PaymentMethod extends Enum
{
    protected static function values(): array
    {
        return [
            'CASH' => 'cash',
            'CREDIT_CARD' => 'credit_card',
            'BANK_TRANSFER' => 'bank_transfer',
            'MOBILE_MONEY' => 'mobile_money',
        ];
    }
}
