<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self NORMAL()
 * @method static self FAST()
 */
class DeliveryType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'NORMAL' => 'Standard',
            'FAST' => 'Express',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'NORMAL' => 'normal',
            'FAST' => 'fast',
        ];
    }
}
