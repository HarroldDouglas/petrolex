<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self EMAIL()
 * @method static self PHONE()
 */
class LoginChannel extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'email' => 'Email',
            'phone' => 'Numéro de téléphone',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'email' => 'email',
            'phone' => 'phone_number',
        ];
    }
}
