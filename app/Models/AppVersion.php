<?php

namespace App\Models;

use App\Enums\AppType;
use App\Enums\Platform;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property AppType $app_type
 * @property Platform $platform
 * @property int $version_code
 * @property string $version_name
 * @property bool $update_required
 * @property string|null $release_notes
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * // Query Scopes
 *
 * @method static Builder forApp(string $appType)
 * @method static Builder forPlatform(string $platform)
 * @method static Builder active()
 * @method static Builder latestVersion()
 */
class AppVersion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'app_type',
        'platform',
        'version_code',
        'version_name',
        'update_required',
        'release_notes',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'app_type' => AppType::class,
        'platform' => Platform::class,
        'version_code' => 'integer',
        'update_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to only include versions for a specific app type.
     */
    public function scopeForApp(Builder $query, string $appType): Builder
    {
        return $query->where('app_type', $appType);
    }

    /**
     * Scope a query to only include versions for a specific platform.
     */
    public function scopeForPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope a query to only include active versions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to get the latest version by version code.
     */
    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderBy('version_code', 'desc');
    }

    /**
     * Get the latest active version for a specific app and platform.
     */
    public static function getLatestVersion(string $appType, string $platform): ?self
    {
        return self::forApp($appType)
            ->forPlatform($platform)
            ->active()
            ->latestVersion()
            ->first();
    }

    /**
     * Get all active versions for a specific app type.
     */
    public static function getVersionsForApp(string $appType): array
    {
        $androidVersion = self::getLatestVersion($appType, Platform::ANDROID()->value);
        $iosVersion = self::getLatestVersion($appType, Platform::IOS()->value);

        return [
            'android' => $androidVersion ? [
                'version_code' => $androidVersion->version_code,
                'version_name' => $androidVersion->version_name,
                'update_required' => $androidVersion->update_required,
                'release_notes' => $androidVersion->release_notes,
            ] : null,
            'ios' => $iosVersion ? [
                'version_code' => $iosVersion->version_code,
                'version_name' => $iosVersion->version_name,
                'update_required' => $iosVersion->update_required,
                'release_notes' => $iosVersion->release_notes,
            ] : null,
        ];
    }
}
