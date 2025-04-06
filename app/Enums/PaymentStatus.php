<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PENDING()
 * @method static self PROCESSING()
 * @method static self PAID()
 * @method static self FAILED()
 * @method static self REFUNDED()
 */
class PaymentStatus extends Enum
{
    protected static function values(): array
    {
        return [
            'PENDING' => 'pending',
            'PROCESSING' => 'processing',
            'PAID' => 'paid',
            'FAILED' => 'failed',
            'REFUNDED' => 'refunded',
        ];
    }
}
