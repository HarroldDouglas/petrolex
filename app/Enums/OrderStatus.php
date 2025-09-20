<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PROCESSING()
 * @method static self DELIVERED()
 * @method static self CANCELLED()
 * @method static self PENDING()
 * @method static self PAID()
 * @method static self FAILED()
 */
class OrderStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'PROCESSING' => 'En cours de livraison',
            'DELIVERED' => 'Livrée',
            'CANCELLED' => 'Annulée',
            'PENDING' => 'En attente de paiement',
            'PAID' => 'Payée',
            'FAILED' => 'Paiement échoué',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'PROCESSING' => 'in_progress',
            'DELIVERED' => 'delivered',
            'CANCELLED' => 'cancelled',
            'PENDING' => 'pending',
            'PAID' => 'paid',
            'FAILED' => 'failed',
        ];
    }

    /**
     * Get the badge CSS class for the status
     */
    public function getBadgeClass(): string
    {
        return match ($this) {
            self::PROCESSING() => 'bg-primary',
            self::DELIVERED() => 'bg-success',
            self::CANCELLED() => 'bg-danger',
            self::PENDING() => 'bg-warning',
            self::PAID() => 'bg-success',
            self::FAILED() => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
