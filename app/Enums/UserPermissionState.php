<?php

namespace App\Enums;

enum UserPermissionState: string
{
    case ROLE = 'role';
    case DIRECT = 'direct';
    case REVOKED = 'revoked';
    case NONE = 'none';

    /**
     * Get the label for the permission state
     */
    public function label(): string
    {
        return match ($this) {
            self::ROLE => 'Hérité du rôle',
            self::DIRECT => 'Permission directe',
            self::REVOKED => 'Permission révoquée',
            self::NONE => 'Non assigné',
        };
    }

    /**
     * Get the CSS class for the permission state
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::ROLE => 'checkbox-role',
            self::DIRECT => 'checkbox-direct',
            self::REVOKED => 'checkbox-revoked',
            self::NONE => 'checkbox-none',
        };
    }

    /**
     * Check if the state represents a selected permission
     */
    public function isSelected(): bool
    {
        return match ($this) {
            self::ROLE, self::DIRECT => true,
            self::REVOKED, self::NONE => false,
        };
    }

    /**
     * Check if the state can be toggled by user action
     */
    public function isToggleable(): bool
    {
        return match ($this) {
            self::ROLE, self::DIRECT, self::REVOKED, self::NONE => true,
        };
    }
}
