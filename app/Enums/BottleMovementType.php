<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self SUPPLIER_DELIVERY()
 * @method static self ASSIGNMENT_TO_DELIVERY()
 * @method static self DELIVERY_TO_CUSTOMER()
 * @method static self RETURN_FROM_CUSTOMER()
 * @method static self DECLARE_LOST_STOLEN()
 * @method static self RETURN_TO_SUPPLIER()
 */
class BottleMovementType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'SUPPLIER_DELIVERY' => 'Livraison fournisseur',
            'ASSIGNMENT_TO_DELIVERY' => 'Affectation au livreur',
            'DELIVERY_TO_CUSTOMER' => 'Livraison au client',
            'RETURN_FROM_CUSTOMER' => 'Retour du client',
            'DECLARE_LOST_STOLEN' => 'Déclaré perdu/volé',
            'RETURN_TO_SUPPLIER' => 'Retour au fournisseur',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'SUPPLIER_DELIVERY' => 'supplier_delivery',
            'ASSIGNMENT_TO_DELIVERY' => 'assignment_to_delivery',
            'DELIVERY_TO_CUSTOMER' => 'delivery_to_customer',
            'RETURN_FROM_CUSTOMER' => 'return_from_customer',
            'DECLARE_LOST_STOLEN' => 'declare_lost_stolen',
            'RETURN_TO_SUPPLIER' => 'return_to_supplier',
        ];
    }
}
