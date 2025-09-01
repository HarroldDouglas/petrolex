<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class GetPrivacyPolicyController extends Controller
{
    /**
     * Get the application privacy policy.
     *
     * Route: GET /privacy-policy
     * Name: api.privacy-policy
     */
    public function __invoke(): ApiResponse
    {
        $htmlContent = Config::get('privacy.content');
        
        return ApiResponse::success(
            data: [
                'html_content' => $htmlContent,
            ],
            message: 'Politique de confidentialité récupérée avec succès.'
        );
    }
}
