<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class TermsAndConditionsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
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
                    'last_updated',
                    'sections',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Conditions d\'utilisation récupérées avec succès.');
    }

    /** @test */
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

    /** @test */
    public function it_returns_correct_metadata(): void
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);

        $data = $response->json('data');
        $configSections = Config::get('terms.sections');
        $configLastUpdated = Config::get('terms.last_updated');

        $this->assertEquals(count($configSections), $data['sections']);
        $this->assertEquals($configLastUpdated, $data['last_updated']);
    }

    /** @test */
    public function it_includes_all_required_sections(): void
    {
        $response = $this->getJson('/api/app/terms-and-conditions');

        $response->assertStatus(200);

        $htmlContent = $response->json('data.html_content');
        $configSections = Config::get('terms.sections');

        foreach ($configSections as $section) {
            $this->assertStringContainsString($section['title'], $htmlContent, "La section '{$section['title']}' doit être présente");
            $this->assertStringContainsString($section['content'], $htmlContent, "Le contenu de la section '{$section['title']}' doit être présent");
        }
    }

    /** @test */
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
        $this->assertArrayHasKey('last_updated', $response->json('data'));
        $this->assertArrayHasKey('sections', $response->json('data'));
    }
}
