<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CREDIT_CARD()
 * @method static self BANK_TRANSFER()
 * @method static self MOBILE_MONEY()
 */
class PaymentMethod extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'CREDIT_CARD' => 'Carte de crédit',
            'BANK_TRANSFER' => 'Virement bancaire',
            'MOBILE_MONEY' => 'Mobile Money',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'CREDIT_CARD' => 'credit_card',
            'BANK_TRANSFER' => 'bank_transfer',
            'MOBILE_MONEY' => 'mobile_money',
        ];
    }
}
