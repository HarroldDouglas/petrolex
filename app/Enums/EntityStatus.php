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
    }
}
