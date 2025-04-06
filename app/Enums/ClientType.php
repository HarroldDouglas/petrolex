<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self INDIVIDUAL()
 * @method static self BUSINESS()
 * @method static self INDUSTRIAL()
 * @method static self RESELLER()
 */
class ClientType extends Enum
{
    protected static function values(): array
    {
        return [
            'INDIVIDUAL' => 'individual',
            'BUSINESS' => 'business',
            'INDUSTRIAL' => 'industrial',
            'RESELLER' => 'reseller',
        ];
    }
}
