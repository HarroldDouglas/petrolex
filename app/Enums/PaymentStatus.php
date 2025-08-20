<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PENDING()
 * @method static self PROCESSING()
 * @method static self PAID()
 * @method static self FAILED()
 * @method static self REFUNDED()
 */
class PaymentStatus extends Enum
{
    public static function labels(): array
    {
        return [
            'PENDING' => 'En attente',
            'PROCESSING' => 'En cours',
            'PAID' => 'Payé',
            'FAILED' => 'Échoué',
            'REFUNDED' => 'Remboursé',
        ];
    }

    public static function values(): array
    {
        return [
            'PENDING' => 'pending',
            'PROCESSING' => 'processing',
            'PAID' => 'paid',
            'FAILED' => 'failed',
            'REFUNDED' => 'refunded',
        ];
    }

    /**
     * Get the CSS class for the status badge
     *
     * @return string[]
     */
    public static function badgeClasses(): array
    {
        return [
            'pending' => 'bg-warning',
            'paid' => 'bg-success',
            'failed' => 'bg-danger',
        ];
    }

    /**
     * Get the badge class for this payment status instance
     */
    public function badgeClass(): string
    {
        return static::badgeClasses()[$this->value] ?? 'bg-secondary';
    }
}
