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
        $privacy = Config::get('privacy');
        
        $htmlContent = $this->buildHtmlContent($privacy);
        
        return ApiResponse::success(
            data: [
                'html_content' => $htmlContent,
                'last_updated' => $privacy['last_updated'],
                'sections' => count($privacy['sections'])
            ],
            message: 'Politique de confidentialité récupérée avec succès.'
        );
    }
    
    /**
     * Build structured HTML content for privacy policy.
     */
    private function buildHtmlContent(array $privacy): string
    {
        $html = '';
        
        $html .= '<h1 style="font-weight: bold; font-size: 18px; margin-bottom: 12px;">' . $privacy['title'] . '</h1>';
        
        $html .= '<p style="margin-bottom: 16px; color: #666; font-size: 14px;">Dernière mise à jour : ' . $privacy['last_updated'] . '</p>';
        
        $html .= '<p style="margin-bottom: 20px; line-height: 1.6;">' . $privacy['introduction'] . '</p>';
        
        foreach ($privacy['sections'] as $section) {
            $html .= '<h2 style="font-weight: bold; font-size: 16px; margin-bottom: 8px; margin-top: 20px;">' . $section['title'] . '</h2>';
            
            $html .= '<p style="margin-bottom: 16px; line-height: 1.6;">' . $section['content'] . '</p>';
        }
        
        return $html;
    }
}
