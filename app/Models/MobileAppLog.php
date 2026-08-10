<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Error/diagnostic log entry reported by a mobile app (customer, delivery or manager).
 *
 * @property int $id
 * @property string $app_type
 * @property string|null $platform
 * @property string|null $app_version
 * @property string|null $device_model
 * @property string|null $os_version
 * @property int|null $user_id
 * @property string $level
 * @property string $message
 * @property string|null $stack_trace
 * @property array|null $context
 * @property \Illuminate\Support\Carbon|null $occurred_at
 */
class MobileAppLog extends Model
{
    protected $fillable = [
        'app_type',
        'platform',
        'app_version',
        'device_model',
        'os_version',
        'user_id',
        'level',
        'message',
        'stack_trace',
        'context',
        'occurred_at',
    ];

    protected $casts = [
        'context' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
