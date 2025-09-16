<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self XAF()
 * @method static self USD()
 * @method static self EUR()
 */
class Currency extends Enum
{
    public static function labels(): array
    {
        return [
            'XAF' => 'FCFA',
            'USD' => '$',
            'EUR' => '€',
        ];
    }

    public static function values(): array
    {
        return [
            'XAF' => 'XAF',
            'USD' => 'USD',
            'EUR' => 'EUR',
        ];
    }

    public static function decimalPlacesMap(): array
    {
        return [
            'XAF' => 0,
            'USD' => 2,
            'EUR' => 2,
        ];
    }

    /**
     * Get the number of decimal places for this currency
     */
    public function decimalPlaces(): int
    {
        return static::decimalPlacesMap()[$this->value];
    }

    /**
     * Format an amount with this currency
     */
    public function format(float $amount, bool $showSymbol = true): string
    {
        $formatted = number_format($amount, $this->decimalPlaces(), ',', ' ');

        if ($showSymbol) {
            $formatted .= ' '.$this->label;
        }

        return $formatted;
    }
}
