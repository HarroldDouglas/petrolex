<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class PreviewTermsAndConditionsController extends Controller
{
    /**
     * Preview the terms and conditions as HTML.
     *
     * Route: GET /terms-and-conditions/preview
     * Name: api.terms-and-conditions.preview
     */
    public function __invoke(): Response
    {
        $terms = Config::get('terms');

        $htmlContent = $this->buildPreviewHtml($terms);

        return new Response($htmlContent, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
        ]);
    }

    /**
     * Build complete HTML page for preview.
     */
    private function buildPreviewHtml(array $terms): string
    {
        $bodyContent = $this->buildHtmlContent($terms);

        return "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Aperçu - {$terms['title']}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .preview-header {
            background: #007bff;
            color: white;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='preview-header'>
        <h3>🔍 Aperçu des Conditions d'Utilisation - Petrolex App</h3>
        <p>Ceci est un aperçu de ce qui sera affiché dans l'application mobile</p>
    </div>
    <div class='container'>
        {$bodyContent}
    </div>
</body>
</html>";
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
