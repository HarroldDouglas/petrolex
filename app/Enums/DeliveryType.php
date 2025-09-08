<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self NORMAL()
 * @method static self FAST()
 * @method label(): string
 */
class DeliveryType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'normal' => 'Standard',
            'fast' => 'Express',
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

    /**
     * Get the delivery fees for each type.
     */
    public static function fees(): array
    {
        return [
            'normal' => 500,
            'fast' => 1000,
        ];
    }

    /**
     * Get the label for this delivery type instance.
     */
    public function label(): string
    {
        return static::labels()[$this->value];
    }

    /**
     * Get the fee for this delivery type instance.
     */
    public function fee(): int
    {
        return static::fees()[$this->value];
    }
}
