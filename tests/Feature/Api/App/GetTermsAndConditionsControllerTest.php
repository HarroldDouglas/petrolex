<?php

namespace Tests\Feature\Api\App;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GetTermsAndConditionsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_terms_and_conditions()
    {
        $expectedTerms = [
            'title' => 'Test Conditions',
            'last_updated' => '2025-09-01',
            'introduction' => 'Test introduction',
            'sections' => [
                [
                    'title' => 'Test Section',
                    'content' => 'Test content'
                ]
            ]
        ];
        
        Config::set('terms', $expectedTerms);

        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message'
                ],
                'data' => [
                    'html_content',
                    'last_updated',
                    'sections'
                ]
            ])
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Conditions d\'utilisation récupérées avec succès.'
                ],
                'data' => [
                    'last_updated' => '2025-09-01',
                    'sections' => 1
                ]
            ]);

        $responseData = $response->json('data');
        $this->assertNotEmpty($responseData['html_content']);
        $this->assertStringContainsString('<h1', $responseData['html_content']);
        $this->assertStringContainsString('Test Conditions', $responseData['html_content']);
        $this->assertStringContainsString('2025-09-01', $responseData['html_content']);
    }

    public function test_html_content_structure_is_correct()
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);
        
        $htmlContent = $response->json('data.html_content');
        
        $this->assertStringContainsString('<h1 style=', $htmlContent, 'Le titre principal doit être présent');
        $this->assertStringContainsString('Dernière mise à jour', $htmlContent, 'La date de mise à jour doit être présente');
        $this->assertStringContainsString('<h2 style=', $htmlContent, 'Les sous-titres de section doivent être présents');
        $this->assertStringContainsString('<p style=', $htmlContent, 'Les paragraphes doivent être stylés');
    }

    public function test_returns_correct_sections_count()
    {

        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);
        
        $sectionsCount = $response->json('data.sections');
        $configSections = Config::get('terms.sections');
        
        $this->assertEquals(count($configSections), $sectionsCount);
    }
}
