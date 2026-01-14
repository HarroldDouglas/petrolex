<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

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
        // Render the privacy policy view as HTML
        $htmlContent = View::make('privacy-policy')->render();

        return ApiResponse::success(
            data: [
                'html_content' => $htmlContent,
            ],
            message: 'Politique de confidentialité récupérée avec succès.'
        );
    }
}
