<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self FULL()
 * @method static self RECHARGE()
 */
class BottleOrderType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'FULL' => 'Consigne avec recharge',
            'RECHARGE' => 'Recharge',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'FULL' => 'bottle_with_content',
            'RECHARGE' => 'content',
        ];
    }

    /**
     * Get localized labels for bottle order types.
     *
     * @return array<string, array<string, string>>
     */
    public static function localizedLabels(): array
    {
        return [
            'FULL' => [
                'fr' => 'Consigne avec recharge',
                'en' => 'Deposit with refill',
            ],
            'RECHARGE' => [
                'fr' => 'Recharge',
                'en' => 'Refill only',
            ],
        ];
    }

    /**
     * Get the localized label for this bottle order type instance.
     */
    public function getLocalizedLabel(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();
        $labels = static::localizedLabels();

        // Map values to keys
        $valueToKey = [
            'bottle_with_content' => 'FULL',
            'content' => 'RECHARGE',
        ];

        $enumKey = $valueToKey[$this->value] ?? null;

        if (! $enumKey || ! isset($labels[$enumKey])) {
            return $this->label;
        }

        return $labels[$enumKey][$locale] ?? $labels[$enumKey]['fr'];
    }
}
