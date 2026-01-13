<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    protected $fillable = [
        'app_type',
        'platform',
        'version_code',
        'version_name',
        'update_required',
        'release_notes',
        'app_link',
    ];

    protected $casts = [
        'version_code' => 'integer',
        'update_required' => 'boolean',
    ];

    /**
     * Get version info for a specific app type (customer_app, delivery_app)
     */
    public static function getVersionInfo(string $appType): array
    {
        $versions = self::where('app_type', $appType)->get();

        $result = [];
        foreach ($versions as $version) {
            $result[$version->platform] = [
                'version_code' => $version->version_code,
                'version_name' => $version->version_name,
                'update_required' => $version->update_required,
                'release_notes' => $version->release_notes ?? '',
                'app_link' => $version->app_link ?? '',
            ];
        }

        return $result;
    }

    /**
     * Get versions for a specific app (customer_app or delivery_app)
     * Returns both android and ios versions, null if not configured
     */
    public static function getVersionsForApp(string $appType): array
    {
        $versions = self::where('app_type', $appType)->get()->keyBy('platform');

        return [
            'android' => isset($versions['android']) ? [
                'version_code' => $versions['android']->version_code,
                'version_name' => $versions['android']->version_name,
                'update_required' => $versions['android']->update_required,
                'release_notes' => $versions['android']->release_notes,
                'app_link' => $versions['android']->app_link,
            ] : null,
            'ios' => isset($versions['ios']) ? [
                'version_code' => $versions['ios']->version_code,
                'version_name' => $versions['ios']->version_name,
                'update_required' => $versions['ios']->update_required,
                'release_notes' => $versions['ios']->release_notes,
                'app_link' => $versions['ios']->app_link,
            ] : null,
        ];
    }
}
