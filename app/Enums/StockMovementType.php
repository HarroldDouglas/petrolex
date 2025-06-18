<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self ENTRY()
 * @method static self EXIT()
 * @method static self RECHARGE()
 * @method static self LOST_STOLEN()
 */
class StockMovementType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'ENTRY' => 'Entrée',
            'EXIT' => 'Sortie',
            'RECHARGE' => 'Recharge',
            'LOST_STOLEN' => 'Perdue/Volée',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'ENTRY' => 'entry',
            'EXIT' => 'exit',
            'RECHARGE' => 'recharge',
            'LOST_STOLEN' => 'lost_stolen',
        ];
    }

    /**
     * Get the CSS class for the badge
     */
    public function cssClasse(): string
    {
        return match ($this->value) {
            'entry' => 'success',
            'exit' => 'danger',
            'recharge' => 'info',
            'lost_stolen' => 'warning',
            default => 'info',
        };
    }
}
