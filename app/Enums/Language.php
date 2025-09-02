<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self FRENCH()
 * @method static self ENGLISH()
 */
final class Language extends Enum
{
    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'FRENCH' => 'fr',
            'ENGLISH' => 'en',
        ];
    }

    public static function default(): string
    {
        return 'fr';
    }

    public static function getValues(): array
    {
        return array_values(self::values());
    }
}
