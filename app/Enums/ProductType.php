<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self BOTTLE()
 * @method static self ACCESSORY()
 */
class ProductType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'BOTTLE' => 'Bouteille',
            'ACCESSORY' => 'Accessoire',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'BOTTLE' => 'bottle',
            'ACCESSORY' => 'accessory',
        ];
    }

    public function getModelClass(): string
    {
        return match ($this->value) {
            'bottle' => \App\Models\BottleType::class,
            'accessory' => \App\Models\AccessoryType::class,
        };
    }
}
