<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self INCOMING()
 * @method static self OUTGOING()
 */
class SupplierDeliveryBottleMovementType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'INCOMING' => 'Entrant',
            'OUTGOING' => 'Sortant',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'INCOMING' => 'incoming',
            'OUTGOING' => 'outgoing',
        ];
    }
}
