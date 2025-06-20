<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CONFIRMED()
 * @method static self PROCESSING()
 * @method static self DELIVERED()
 * @method static self CANCELLED()
 */
class OrderStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'CONFIRMED' => 'Confirmée',
            'PROCESSING' => 'En cours de livraison',
            'DELIVERED' => 'Livrée',
            'CANCELLED' => 'Annulée',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'CONFIRMED' => 'confirmed',
            'PROCESSING' => 'in_progress',
            'DELIVERED' => 'delivered',
            'CANCELLED' => 'cancelled',
        ];
    }

    /**
     * Get the badge CSS class for the status
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::CONFIRMED() => 'bg-dark',
            self::PROCESSING() => 'bg-primary',
            self::DELIVERED() => 'bg-success',
            self::CANCELLED() => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
