<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PENDING()
 * @method static self PAID()
 * @method static self FAILED()
 */
class PaymentStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'PENDING' => 'En attente',
            'PAID' => 'Payé',
            'FAILED' => 'Échoué',
        ];
    }

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return [
            'PENDING' => 'pending',
            'PAID' => 'paid',
            'FAILED' => 'failed',
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
