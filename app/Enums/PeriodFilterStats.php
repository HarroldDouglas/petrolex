<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self ONE_WEEK()
 * @method static self TWO_WEEKS()
 * @method static self ONE_MONTH()
 * @method static self TWO_MONTHS()
 * @method static self THREE_MONTHS()
 * @method static self CUSTOM()
 */
class PeriodFilterStats extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'ONE_WEEK' => 'Il y a une semaine',
            'TWO_WEEKS' => 'Il y a deux semaines',
            'ONE_MONTH' => 'Il y a 1 mois',
            'TWO_MONTHS' => 'Il y a 2 mois',
            'THREE_MONTHS' => 'Il y a 3 mois',
            'CUSTOM' => 'Date personnalisée',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'ONE_WEEK' => '1week',
            'TWO_WEEKS' => '2weeks',
            'ONE_MONTH' => '1month',
            'TWO_MONTHS' => '2months',
            'THREE_MONTHS' => '3months',
            'CUSTOM' => 'custom',
        ];
    }

    /**
     * Get the default value for the enum.
     */
    public static function default(): string
    {
        return self::ONE_WEEK()->value;
    }
}
