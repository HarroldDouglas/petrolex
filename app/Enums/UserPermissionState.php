<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self ROLE()
 * @method static self DIRECT()
 * @method static self REVOKED()
 * @method static self NONE()
 */
class UserPermissionState extends Enum
{
    /**
     * Get the label for the permission state
     */
    public static function labels(): array
    {
        return [
            'ROLE' => 'Hérité du rôle',
            'DIRECT' => 'Permission directe',
            'REVOKED' => 'Permission révoquée',
            'NONE' => 'Non assigné',
        ];
    }

    /**
     * Get the values for the permission states
     */
    public static function values(): array
    {
        return [
            'ROLE' => 'role',
            'DIRECT' => 'direct',
            'REVOKED' => 'revoked',
            'NONE' => 'none',
        ];
    }

    /**
     * Get the CSS class for the permission state
     */
    public static function cssClasses(): array
    {
        return [
            'role' => 'checkbox-role',
            'direct' => 'checkbox-direct',
            'revoked' => 'checkbox-revoked',
            'none' => 'checkbox-none',
        ];
    }

    /**
     * Get CSS class for this instance
     */
    public function cssClass(): string
    {
        return static::cssClasses()[$this->value];
    }

    /**
     * Check if the state represents a selected permission
     */
    public function isSelected(): bool
    {
        return in_array($this->value, ['role', 'direct']);
    }

    /**
     * Get states that represent selected permissions
     */
    public static function selectedStates(): array
    {
        return ['role', 'direct'];
    }

    /**
     * Get states that represent unselected permissions
     */
    public static function unselectedStates(): array
    {
        return ['revoked', 'none'];
    }
}
