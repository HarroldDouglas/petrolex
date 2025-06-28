<?php

namespace App\Models;

use App\Enums\NotificationType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property NotificationType $type
 * @property int $notifiable_id
 * @property string $notifiable_type
 * @property array $data
 * @property Carbon|null $read_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * // Relations
 * @property-read \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection $notifiable
 *
 * // Accessors
 * @property-read string $time_ago
 *
 * // Query Scopes
 *
 * @method static \Illuminate\Database\Eloquent\Builder unread()
 * @method static \Illuminate\Database\Eloquent\Builder read()
 * @method static \Illuminate\Database\Eloquent\Builder forType(NotificationType $type)
 */
class Notification extends Model
{
    protected $fillable = [
        'type',
        'notifiable_type',
        'notifiable_id',
        'data',
        'read_at',
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => now()])->save();
        }
    }

    public function markAsUnread(): void
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }

    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeForType(Builder $query, NotificationType $type): Builder
    {
        return $query->where('type', $type);
    }
}
