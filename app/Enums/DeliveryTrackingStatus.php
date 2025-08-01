<?php

declare(strict_types=1);

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self PENDING()
 * @method static self STARTED()
 * @method static self IN_PROGRESS()
 * @method static self DELIVERED()
 * @method static self CANCELLED()
 */
final class DeliveryTrackingStatus extends Enum
{
    /**
     * @return string[]
     */
    public static function labels(): array
    {
        return [
            'PENDING' => 'En attente',
            'STARTED' => 'Démarrée',
            'IN_PROGRESS' => 'En cours',
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
            'PENDING' => 'pending',
            'STARTED' => 'started',
            'IN_PROGRESS' => 'in_progress',
            'DELIVERED' => 'delivered',
            'CANCELLED' => 'cancelled',
        ];
    }
}
