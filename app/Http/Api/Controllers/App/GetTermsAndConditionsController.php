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
        $terms = Config::get('terms');

        $htmlContent = $this->buildHtmlContent($terms);

        return ApiResponse::success(
            data: [
                'html_content' => $htmlContent,
                'last_updated' => $terms['last_updated'],
                'sections' => count($terms['sections']),
            ],
            message: 'Conditions d\'utilisation récupérées avec succès.'
        );
    }

    /**
     * Build structured HTML content for terms and conditions.
     */
    private function buildHtmlContent(array $terms): string
    {
        $html = '';

        $html .= '<h1 style="font-weight: bold; font-size: 18px; margin-bottom: 12px;">'.$terms['title'].'</h1>';

        $html .= '<p style="margin-bottom: 16px; color: #666; font-size: 14px;">Dernière mise à jour : '.$terms['last_updated'].'</p>';

        $html .= '<p style="margin-bottom: 20px; line-height: 1.6;">'.$terms['introduction'].'</p>';

        foreach ($terms['sections'] as $section) {

            $html .= '<h2 style="font-weight: bold; font-size: 16px; margin-bottom: 8px; margin-top: 20px;">'.$section['title'].'</h2>';

            $html .= '<p style="margin-bottom: 16px; line-height: 1.6;">'.$section['content'].'</p>';
        }

        return $html;
    }
}
