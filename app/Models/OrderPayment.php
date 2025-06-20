<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * OrderPayment model represents the payment details for an order.
 *
 * * @property int $id
 * @property int $order_id
 * @property string $payment_reference
 * @property PaymentStatus $payment_status
 * @property PaymentMethod $payment_method
 * @property float $amount_paid
 * @property float $amount_due
 * @property \Illuminate\Support\Carbon|null $payment_date
 * @property string|null $payment_notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class OrderPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'payment_reference',
        'payment_status',
        'payment_method',
        'amount_paid',
        'amount_due',
        'payment_date',
        'payment_notes',
    ];

    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'payment_date' => 'datetime',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
    ];

    /**
     * Get the order that owns the payment.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
