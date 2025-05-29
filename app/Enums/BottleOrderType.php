<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self BOTTLE_WITH_CONTENT()
 * @method static self CONTENT()
 */
class BottleOrderType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'BOTTLE_WITH_CONTENT' => 'Bouteille avec recharge',
            'CONTENT' => 'Recharge',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'BOTTLE_WITH_CONTENT' => 'bottle_with_content',
            'CONTENT' => 'content',
        ];
    }
}
