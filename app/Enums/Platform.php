<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self ANDROID()
 * @method static self IOS()
 */
class Platform extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'ANDROID' => 'Android',
            'IOS' => 'iOS',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'ANDROID' => 'android',
            'IOS' => 'ios',
        ];
    }
}
