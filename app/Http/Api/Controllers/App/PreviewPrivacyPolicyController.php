<?php

namespace App\Http\Api\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

class PreviewPrivacyPolicyController extends Controller
{
    /**
     * Preview the privacy policy as rendered HTML.
     *
     * Route: GET /privacy-policy/preview
     * Name: api.privacy-policy.preview
     */
    public function __invoke(): Response
    {
        $htmlContent = Config::get('privacy.content');
        
        $fullHtml = $this->wrapInHtmlDocument($htmlContent);
        
        return new Response($fullHtml, Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=utf-8'
        ]);
    }
    
    /**
     * Wrap content in a complete HTML document for preview.
     */
    private function wrapInHtmlDocument(string $content): string
    {
        return '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévisualisation - Politique de confidentialité</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .preview-header {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
        .preview-header h3 {
            margin: 0;
            color: #1976d2;
            font-size: 16px;
        }
        .preview-header p {
            margin: 5px 0 0 0;
            color: #555;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="preview-header">
            <h3>🔍 Prévisualisation de la Politique de confidentialité</h3>
            <p>Voici comment le contenu HTML sera affiché dans l\'application mobile</p>
        </div>
        
        <div class="content">
            ' . $content . '
        </div>
    </div>
</body>
</html>';
    }
}
