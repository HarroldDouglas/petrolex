<?php

namespace App\Http\Api\Resources;

use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WalletTransaction
 */
class WalletTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var WalletTransaction $transaction */
        $transaction = $this->resource;

        return [
            'id' => $transaction->id,
            'type' => $transaction->type->value,
            'type_label' => $transaction->type->value === 'credit'
                ? __('wallet.transaction_credit')
                : __('wallet.transaction_debit'),
            'amount' => (float) $transaction->amount,
            'formatted_amount' => number_format((float) $transaction->amount, 0, ',', ' ').' FCFA',
            'balance_before' => (float) $transaction->balance_before,
            'balance_after' => (float) $transaction->balance_after,
            'reference' => $transaction->reference,
            'description' => $transaction->description,
            'order_id' => $transaction->order_id,
            'order_number' => $transaction->order?->order_number,
            'created_at' => $transaction->created_at->toISOString(),
            'metadata' => $transaction->metadata,
        ];
    }
}
