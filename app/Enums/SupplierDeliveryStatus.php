<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self IN_PROGRESS()
 * @method static self COMPLETED()
 * @method static self CANCELLED()
 */
class SupplierDeliveryStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'IN_PROGRESS' => 'En cours',
            'COMPLETED' => 'Terminée',
            'CANCELLED' => 'Annulée',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'IN_PROGRESS' => 'processing',
            'COMPLETED' => 'completed',
            'CANCELLED' => 'cancelled',
        ];
    }

    /**
     * Get the CSS class for the badge
     */
    public function badge(): string
    {
        return match ($this->value) {
            'processing' => 'text-outline-warning',
            'completed' => 'text-outline-success',
            'cancelled' => 'text-outline-danger',
            default => 'text-outline-secondary',
        };
    }
}
