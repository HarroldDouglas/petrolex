<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self email()
 * @method static self phone()
 */
class LoginChannel extends Enum
{
    protected static function values(): array
    {
        return [
            'email' => 'email',
            'phone' => 'phone_number',
        ];
    }
}
