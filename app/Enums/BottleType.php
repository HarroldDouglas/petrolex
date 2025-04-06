<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self SMALL()
 * @method static self MEDIUM()
 * @method static self LARGE()
 * @method static self INDUSTRIAL_100()
 */
class BottleType extends Enum
{
    protected static function values(): array
    {
        return [
            'SMALL' => 'small',
            'MEDIUM' => 'medium',
            'LARGE' => 'large',
        ];
    }

    public static function getSpecification(string $type)
    {
        return config('bottle_types.specifications.'.$type);
    }

    public static function getAllSpecifications()
    {
        return config('bottle_types.specifications');
    }
}
