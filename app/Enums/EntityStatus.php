<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self ACTIVE()
 * @method static self INACTIVE()
 */
class EntityStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'ACTIVE' => 'Actif',
            'INACTIVE' => 'Inactif',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'ACTIVE' => 'active',
            'INACTIVE' => 'inactive',
        ];
    }

    public static function classes(): array
    {
        return [
            'ACTIVE' => 'success',
            'INACTIVE' => 'secondary',
        ];
    }
}
