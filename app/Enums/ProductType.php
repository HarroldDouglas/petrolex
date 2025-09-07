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

    /**
     * Get mobile labels for product types.
     *
     * @return array<string, array<string, string>>
     */
    public static function mobileLabels(): array
    {
        return [
            'BOTTLE' => [
                'fr' => 'Bouteilles à gaz domestiques',
                'en' => 'Domestic Gas Bottles',
            ],
            'ACCESSORY' => [
                'fr' => 'Accessoires de sécurité et distributions',
                'en' => 'Safety and Distribution Accessories',
            ],
        ];
    }

    /**
     * Get the mobile label for this product type instance.
     */
    public function labelForMobile(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $labels = static::mobileLabels();
        $upperValue = strtoupper($this->value);

        if (! isset($labels[$upperValue])) {
            return $this->label;
        }

        return $labels[$upperValue][$locale] ?? $labels[$upperValue]['fr'];
    }
}
