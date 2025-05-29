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
<<<<<<< HEAD
           'ACTIVE' => 'actif',
           'INACTIVE' => 'inactif',
=======
            'ACTIVE' => 'Actif',
            'INACTIVE' => 'Inactif',
>>>>>>> dev
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
<<<<<<< HEAD
            'ACTIVE' => 1,
            'INACTIVE' => 0,
        ];
    }

    public function badge(): string
    {
        return $this->isActive() ? 'success' : 'danger';
    }

    public function isActive(): bool
    {
        return $this->equals(self::ACTIVE());
=======
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
>>>>>>> dev
    }
}
