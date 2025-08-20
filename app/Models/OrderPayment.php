<?php

namespace App
Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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