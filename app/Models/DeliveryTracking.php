<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeliveryTrackingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents the tracking of a specific delivery.
 *
 * @property int $id
 * @property int $order_id
 * @property DeliveryTrackingStatus $status
 * @property float|null $driver_lat
 * @property float|null $driver_lng
 * @property int|null $estimated_duration
 * @property float|null $distance_remaining
 * @property float|null $current_speed
 * @property array<string, mixed>|null $route_geometry
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property-read Order $order
 */
final class DeliveryTracking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'status',
        'driver_lat',
        'driver_lng',
        'estimated_duration',
        'distance_remaining',
        'current_speed',
        'route_geometry',
        'started_at',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => DeliveryTrackingStatus::class,
        'route_geometry' => 'array',
        'started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'driver_lat' => 'decimal:8',
        'driver_lng' => 'decimal:8',
        'current_speed' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
