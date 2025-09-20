<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self ORANGE_MONEY()
 * @method static self MTN_MONEY()
 * @method static self CREDIT_CARD()
 */
class PaymentMethod extends Enum
{
    public static function labels(): array
    {
        return [
            'ORANGE_MONEY' => 'Orange Money',
            'MTN_MONEY' => 'MTN Money',
            'CREDIT_CARD' => 'Carte Bancaire',
        ];
    }

    public static function values(): array
    {
        return [
            'ORANGE_MONEY' => 'orange_money',
            'MTN_MONEY' => 'mtn_money',
            'CREDIT_CARD' => 'credit_card',
        ];
    }
}
