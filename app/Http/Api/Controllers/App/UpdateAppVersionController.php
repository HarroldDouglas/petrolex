<?php

namespace App\Http\Api\Controllers\App;

use App\Enums\AppType;
use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateAppVersionController extends Controller
{
    /**
     * Update or create app version info for both platforms in one call.
     *
     * Route: PUT /api/app/version/{app_type}
     *        app_type = customer_app | delivery_app
     * Header: X-Mobile-Dev-Token: <token>
     *
     * Body (au moins une plateforme requise):
     * {
     *   "android": {
     *     "version_code": 12,
     *     "version_name": "1.0.12",
     *     "update_required": true,
     *     "release_notes": "...",   // optionnel
     *     "app_link": "..."         // optionnel
     *   },
     *   "ios": { ... }              // optionnel
     * }
     */
    public function __invoke(Request $request, string $appType): ApiResponse
    {
        if (! in_array($appType, AppType::values())) {
            return ApiResponse::error(
                message: 'Type d\'application invalide. Valeurs acceptées : ' . implode(', ', AppType::values()),
                statusCode: 422
            );
        }

        $platformRules = [
            'version_code'    => ['required', 'integer', 'min:1'],
            'version_name'    => ['required', 'string', 'max:20'],
            'update_required' => ['required', 'boolean'],
            'release_notes'   => ['nullable', 'string', 'max:500'],
            'app_link'        => ['nullable', 'string', 'url', 'max:500'],
        ];

        $validated = $request->validate([
            'android'                  => ['nullable', 'array'],
            'android.version_code'     => $platformRules['version_code'],
            'android.version_name'     => $platformRules['version_name'],
            'android.update_required'  => $platformRules['update_required'],
            'android.release_notes'    => $platformRules['release_notes'],
            'android.app_link'         => $platformRules['app_link'],
            'ios'                      => ['nullable', 'array'],
            'ios.version_code'         => $platformRules['version_code'],
            'ios.version_name'         => $platformRules['version_name'],
            'ios.update_required'      => $platformRules['update_required'],
            'ios.release_notes'        => $platformRules['release_notes'],
            'ios.app_link'             => $platformRules['app_link'],
        ]);

        if (empty($validated['android']) && empty($validated['ios'])) {
            return ApiResponse::error(
                message: 'Au moins une plateforme (android ou ios) est requise.',
                statusCode: 422
            );
        }

        $updated = [];

        foreach (['android', 'ios'] as $platform) {
            if (! empty($validated[$platform])) {
                $data = $validated[$platform];

                AppVersion::updateOrCreate(
                    ['app_type' => $appType, 'platform' => $platform],
                    [
                        'version_code'    => $data['version_code'],
                        'version_name'    => $data['version_name'],
                        'update_required' => $data['update_required'],
                        'release_notes'   => $data['release_notes'] ?? null,
                        'app_link'        => $data['app_link'] ?? null,
                    ]
                );

                $updated[$platform] = $data;
            }
        }

        return ApiResponse::success(
            data: ['app_type' => $appType, ...$updated],
            message: 'Version(s) mise(s) à jour avec succès.'
        );
    }
}
