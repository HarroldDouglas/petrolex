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
            'IN_PROGRESS' => 'in_progress',
            'COMPLETED' => 'completed',
            'CANCELLED' => 'cancelled',
        ];
    }
}
