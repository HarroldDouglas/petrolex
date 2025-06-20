<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * This model represent bottle that has been scanned for an order, so the bottle has been associated with an order item.
 *
 * @property int $id
 * @property int $order_item_id
 * @property int $bottle_id
 * @property OrderItem $orderItem
 * @property Bottle $bottle
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class OrderItemBottle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_item_id',
        'bottle_id',
    ];

    /**
     * Get the order item that owns this bottle.
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the bottle associated with this order item.
     */
    public function bottle(): BelongsTo
    {
        return $this->belongsTo(Bottle::class);
    }
}
