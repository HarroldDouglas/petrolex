<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TermsAndConditionsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_retrieve_terms_and_conditions(): void
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    'html_content',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Conditions d\'utilisation récupérées avec succès.');
    }

    #[Test]
    public function it_returns_properly_formatted_html_content(): void
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);

        $data = $response->json('data');

        $this->assertNotEmpty($data['html_content']);

        $htmlContent = $data['html_content'];
        $this->assertStringContainsString('<h1 style=', $htmlContent, 'Le titre principal doit être présent');
        $this->assertStringContainsString('Conditions d\'utilisation', $htmlContent, 'Le titre doit contenir "Conditions d\'utilisation"');
        $this->assertStringContainsString('Dernière mise à jour', $htmlContent, 'La date de mise à jour doit être présente');
        $this->assertStringContainsString('<h2 style=', $htmlContent, 'Les sous-titres de section doivent être présents');
        $this->assertStringContainsString('<p style=', $htmlContent, 'Les paragraphes doivent être stylés');
    }

    #[Test]
    public function it_returns_correct_metadata(): void
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Vérifier que le contenu HTML est présent et non vide
        $this->assertNotEmpty($data['html_content']);
        $this->assertStringContainsString('Conditions d\'utilisation', $data['html_content']);
        $this->assertStringContainsString('2025-09-01', $data['html_content']);
    }

    #[Test]
    public function it_includes_all_required_sections(): void
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);

        $htmlContent = $response->json('data.html_content');

        // Vérifier que toutes les sections requises sont présentes dans le HTML
        $this->assertStringContainsString('Collecte des données', $htmlContent, "La section 'Collecte des données' doit être présente");
        $this->assertStringContainsString('Protection des données', $htmlContent, "La section 'Protection des données' doit être présente");
    }

    #[Test]
    public function it_returns_valid_json_structure(): void
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Conditions d\'utilisation récupérées avec succès.',
                ],
            ]);

        $this->assertArrayHasKey('html_content', $response->json('data'));
    }
}
