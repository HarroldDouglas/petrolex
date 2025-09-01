<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Api\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

class GetTermsAndConditionsController extends Controller
{
    /**
     * Get the application terms and conditions.
     *
     * Route: GET /terms-and-conditions
     * Name: api.terms-and-conditions
     */
    public function __invoke(): ApiResponse
    {
        
        return ApiResponse::success(
            data: [
                'html_content' => Config::get('terms.content'),
            ],
            message: 'Conditions d\'utilisation récupérées avec succès.'
        );
    }
}
