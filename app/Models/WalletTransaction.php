<?php

namespace App\Models;

use App\Enums\WalletTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $order_id
 * @property WalletTransactionType $type
 * @property float $amount
 * @property float $balance_before
 * @property float $balance_after
 * @property string $reference
 * @property string|null $description
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read Customer $customer
 * @property-read Order|null $order
 */
class WalletTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'order_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'reference',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'type' => WalletTransactionType::class,
    ];

    /**
     * Get the customer that owns this transaction.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the order associated with this transaction.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Check if this is a credit transaction.
     */
    public function isCredit(): bool
    {
        return $this->type->equals(WalletTransactionType::CREDIT());
    }

    /**
     * Check if this is a debit transaction.
     */
    public function isDebit(): bool
    {
        return $this->type->equals(WalletTransactionType::DEBIT());
    }

    /**
     * Generate a unique reference for the transaction.
     */
    public static function generateReference(): string
    {
        return 'WT_'.strtoupper(uniqid()).'_'.now()->format('YmdHis');
    }
}
