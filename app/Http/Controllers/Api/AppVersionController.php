<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\JsonResponse;

class AppVersionController extends Controller
{
    /**
     * Get app version info
     *
     * @param string $appType customer_app or delivery_app
     * @return JsonResponse
     */
    public function show(string $appType): JsonResponse
    {
        // Validate app_type
        if (!in_array($appType, ['customer_app', 'delivery_app'])) {
            return response()->json([
                '_metadata' => [
                    'success' => false,
                    'message' => 'Invalid app_type. Must be customer_app or delivery_app',
                ],
                'data' => null,
            ], 400);
        }

        $versions = AppVersion::getVersionInfo($appType);

        // If no versions found, return default
        if (empty($versions)) {
            $versions = [
                'android' => [
                    'version_code' => 1,
                    'version_name' => '1.0.0',
                    'update_required' => false,
                    'release_notes' => '',
                ],
                'ios' => [
                    'version_code' => 1,
                    'version_name' => '1.0.0',
                    'update_required' => false,
                    'release_notes' => '',
                ],
            ];
        }

        return response()->json($versions);
    }
}
