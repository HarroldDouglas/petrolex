<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $order_id
 * @property PaymentMethod $payment_method
 * @property float $amount_paid
 * @property float $amount_due
 * @property PaymentStatus $payment_status
 * @property string $payment_reference
 * @property string|null $transaction_reference
 * @property string|null $payment_url
 * @property array|null $gateway_response
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property string|null $payment_notes
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Order $order
 */
class OrderPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount_paid',
        'amount_due',
        'payment_status',
        'payment_reference',
        'transaction_reference',
        'payment_url',
        'gateway_response',
        'payment_date',
        'payment_notes',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
        'payment_date' => 'datetime',
        'gateway_response' => 'array',
        'payment_method' => PaymentMethod::class,
        'payment_status' => PaymentStatus::class,
    ];

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
