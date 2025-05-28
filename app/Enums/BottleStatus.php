<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self IN_STOCK()
 * @method static self WITH_DELIVERY_PERSON()
 * @method static self WITH_CLIENT()
 * @method static self LOST_STOLEN()
 * @method static self RETURNED_TO_SUPPLIER()
 */
class BottleStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'IN_STOCK' => 'En stock',
            'WITH_DELIVERY_PERSON' => 'Avec un livreur',
            'WITH_CLIENT' => 'Chez un client',
            'LOST_STOLEN' => 'Perdue/Volée',
            'RETURNED_TO_SUPPLIER' => 'Retournée au fournisseur',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'IN_STOCK' => 'in_stock',
            'WITH_DELIVERY_PERSON' => 'with_delivery_person',
            'WITH_CLIENT' => 'with_client',
            'LOST_STOLEN' => 'lost_stolen',
            'RETURNED_TO_SUPPLIER' => 'returned_to_supplier',
        ];
    }
}
