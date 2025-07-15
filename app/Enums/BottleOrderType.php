<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self FULL()
 * @method static self RECHARGE()
 */
class BottleOrderType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'FULL' => 'Bouteille avec recharge',
            'RECHARGE' => 'Recharge',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'FULL' => 'bottle_with_content',
            'RECHARGE' => 'content',
        ];
    }
}