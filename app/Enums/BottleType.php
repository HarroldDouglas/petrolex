<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self SMALL()
 * @method static self MEDIUM()
 * @method static self LARGE()
 */
class BottleType extends Enum
{
    public static function getSpecification(string $type)
    {
        return config('bottle_types.specifications.'.$type);
    }

    public static function getAllSpecifications()
    {
        return config('bottle_types.specifications');
    }
}
