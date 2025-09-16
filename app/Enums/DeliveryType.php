<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self NORMAL()
 * @method static self FAST()
 */
class DeliveryType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'NORMAL' => 'Standard',
            'FAST' => 'Express',
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
     * Get the delivery descriptions for each type.
     */
    public static function descriptions(?string $locale = null): array
    {
        $originalLocale = null;
        if ($locale && $locale !== app()->getLocale()) {
            $originalLocale = app()->getLocale();
            app()->setLocale($locale);
        }

        $descriptions = [
            'normal' => __('delivery.descriptions.normal'),
            'fast' => __('delivery.descriptions.fast'),
        ];

        if ($originalLocale) {
            app()->setLocale($originalLocale);
        }

        return $descriptions;
    }

    /**
     * Get the fee for this delivery type instance.
     */
    public function fee(): int
    {
        return static::fees()[$this->value];
    }

    /**
     * Get the description for this delivery type instance.
     */
    public function description(?string $locale = null): string
    {
        return static::descriptions($locale)[$this->value];
    }
}
