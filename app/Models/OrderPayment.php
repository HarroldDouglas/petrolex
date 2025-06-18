<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
