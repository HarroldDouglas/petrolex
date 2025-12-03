<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CUSTOMER_APP()
 * @method static self DELIVERY_APP()
 */
class AppType extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'CUSTOMER_APP' => 'Application Client',
            'DELIVERY_APP' => 'Application Livreur',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'CUSTOMER_APP' => 'customer_app',
            'DELIVERY_APP' => 'delivery_app',
        ];
    }
}
