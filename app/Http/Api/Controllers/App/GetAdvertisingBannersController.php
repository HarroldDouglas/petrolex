<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class GetAdvertisingBannersController extends Controller
{
    /**
     * Get the application advertising banners.
     *
     * Route: GET /advertising/banners
     * Name: api.app.advertising.banners
     */
    public function __invoke(): ApiResponse
    {
        $banners = Config::get('advertising.banners', []);

        // Filter active banners and sort by priority
        $activeBanners = collect($banners)
            ->filter(function ($banner) {
                return $banner['is_active'] ?? false;
            })
            ->filter(function ($banner) {
                $now = now()->format('Y-m-d');
                $startDate = $banner['start_date'] ?? null;
                $endDate = $banner['end_date'] ?? null;

                if ($startDate && $now < $startDate) {
                    return false;
                }

                if ($endDate && $now > $endDate) {
                    return false;
                }

                return true;
            })
            ->map(function ($banner) {
                // Generate full URL for image
                $banner['image_url'] = $banner['image_url']
                    ? asset($banner['image_url'])
                    : null;

                return $banner;
            })
            ->sortBy('priority')
            ->values()
            ->toArray();

        return ApiResponse::success(
            data: [
                'banners' => $activeBanners,
                'total' => count($activeBanners),
            ],
            message: 'Bannières publicitaires récupérées avec succès.'
        );
    }
}
