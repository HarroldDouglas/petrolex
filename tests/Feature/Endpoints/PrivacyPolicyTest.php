<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class PrivacyPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_retrieve_privacy_policy(): void
    {
        $response = $this->getJson('/api/app/privacy-policy');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message'
                ],
                'data' => [
                    'html_content'
                ]
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Politique de confidentialité récupérée avec succès.');
    }

    /** @test */
    public function it_returns_properly_formatted_html_content(): void
    {
        $response = $this->getJson('/api/app/privacy-policy');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Vérifier que le contenu HTML est présent
        $this->assertNotEmpty($data['html_content']);
        
        // Vérifier la structure HTML
        $htmlContent = $data['html_content'];
        $this->assertStringContainsString('<h1 style=', $htmlContent, 'Le titre principal doit être présent');
        $this->assertStringContainsString('Politique de confidentialité', $htmlContent, 'Le titre doit contenir "Politique de confidentialité"');
        $this->assertStringContainsString('Dernière mise à jour', $htmlContent, 'La date de mise à jour doit être présente');
        $this->assertStringContainsString('<h2 style=', $htmlContent, 'Les sous-titres de section doivent être présents');
        $this->assertStringContainsString('<p style=', $htmlContent, 'Les paragraphes doivent être stylés');
    }

    /** @test */
    public function it_returns_correct_metadata(): void
    {
        $response = $this->getJson('/api/app/privacy-policy');

        $response->assertStatus(200);

        $data = $response->json('data');
        $configContent = Config::get('privacy.content');

        // Vérifier que le contenu HTML correspond à la configuration
        $this->assertEquals($configContent, $data['html_content']);
    }

    /** @test */
    public function it_includes_all_required_sections(): void
    {
        $response = $this->getJson('/api/app/privacy-policy');

        $response->assertStatus(200);

        $htmlContent = $response->json('data.html_content');

        // Vérifier que les sections essentielles sont présentes dans le HTML
        $this->assertStringContainsString('Collecte de données', $htmlContent, "La section 'Collecte de données' doit être présente");
        $this->assertStringContainsString('Protection des données', $htmlContent, "La section 'Protection des données' doit être présente");
        
        // Vérifier le contenu spécifique des sections
        $this->assertStringContainsString('informations d\'identification', $htmlContent, "Le contenu sur les informations d'identification doit être présent");
        $this->assertStringContainsString('mesures de sécurité', $htmlContent, "Le contenu sur les mesures de sécurité doit être présent");
    }

    /** @test */
    public function it_returns_valid_json_structure(): void
    {
        $response = $this->getJson('/api/app/privacy-policy');

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Politique de confidentialité récupérée avec succès.'
                ]
            ]);

        // Vérifier que les données obligatoires sont présentes
        $this->assertArrayHasKey('html_content', $response->json('data'));
    }
}
