<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self SUPER_ADMIN()
 * @method static self ADMIN()
 * @method static self MANAGER()
 * @method static self ACCOUNTANT()
 * @method static self GAS_MANAGER()
 * @method static self CENTER_MANAGER()
 * @method static self DELIVERY_PERSON()
 * @method static self CUSTOMER()
 */
class UserRole extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'SUPER_ADMIN' => 'Super Administrateur',
            'ADMIN' => 'Administrateur',
            'MANAGER' => 'Gestionnaire',
            'ACCOUNTANT' => 'Comptable',
            'GAS_MANAGER' => 'Gestionnaire de Gaz',
            'CENTER_MANAGER' => 'Responsable de Centre',
            'DELIVERY_PERSON' => 'Livreur',
            'CUSTOMER' => 'Client',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'SUPER_ADMIN' => 'super_admin',
            'ADMIN' => 'admin',
            'MANAGER' => 'manager',
            'ACCOUNTANT' => 'accountant',
            'GAS_MANAGER' => 'gas_manager',
            'CENTER_MANAGER' => 'center_manager',
            'DELIVERY_PERSON' => 'delivery_person',
            'CUSTOMER' => 'customer',
        ];
    }
}
