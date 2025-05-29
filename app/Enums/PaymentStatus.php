<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PENDING()
 * @method static self PAID()
 * @method static self FAILED()
 * @method static self REFUNDED()
 */
class PaymentStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'PENDING' => 'En attente',
            'PAID' => 'Payé',
            'FAILED' => 'Échoué',
            'REFUNDED' => 'Remboursé',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'PENDING' => 'pending',
            'PAID' => 'paid',
            'FAILED' => 'failed',
            'REFUNDED' => 'refunded',
        ];
    }
}
