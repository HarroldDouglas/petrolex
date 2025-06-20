<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CREDIT_CARD()
 * @method static self MOBILE_MONEY()
 * @method static self ORANGE_MONEY()
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
            'MOBILE_MONEY' => 'Mobile Money',
            'ORANGE_MONEY' => 'Orange Money',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'CREDIT_CARD' => 'credit_card',
            'MOBILE_MONEY' => 'mobile_money',
            'ORANGE_MONEY' => 'orange_money',
        ];
    }
}
