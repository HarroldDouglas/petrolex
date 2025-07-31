<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Represents the tracking of a specific delivery.
 *
 * @property string $order_number
 * @property string $customer_name
 * @property string $driver_name
 * @property string $driver_phone
 * @property string $status
 * @property float $driver_lat
 * @property float $driver_lng
 * @property float $destination_lat
 * @property float $destination_lng
 * @property string $destination_address
 * @property int $estimated_duration
 * @property int $distance_remaining
 * @property array|null $route_geometry
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 */
final class DeliveryTracking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_number',
        'customer_name',
        'driver_name',
        'driver_phone',
        'status',
        'driver_lat',
        'driver_lng',
        'destination_lat',
        'destination_lng',
        'destination_address',
        'estimated_duration',
        'distance_remaining',
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
        'route_geometry' => 'array',
        'started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'driver_lat' => 'decimal:8',
        'driver_lng' => 'decimal:8',
        'destination_lat' => 'decimal:8',
        'destination_lng' => 'decimal:8',
    ];
}