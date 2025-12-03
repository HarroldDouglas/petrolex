<?php

namespace App\Enums;

use Spatie\Enum\Laravel\Enum;

/**
 * @method static self CREDIT()
 * @method static self DEBIT()
 */
final class WalletTransactionType extends Enum
{
    /**
     * @return string[]
     */
    protected static function values(): array
    {
        return [
            'CREDIT' => 'credit',
            'DEBIT' => 'debit',
        ];
    }

    /**
     * @return string[]
     */
    protected static function labels(): array
    {
        return [
            'CREDIT' => 'Crédit',
            'DEBIT' => 'Débit',
        ];
    }

    public function description(): string
    {
        return match ($this->value) {
            'credit' => __('wallet.transaction_credit'),
            'debit' => __('wallet.transaction_debit'),
            default => $this->value,
        };
    }
}
