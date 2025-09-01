<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

final class SupportContactTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_retrieve_support_contact_information(): void
    {
        $response = $this->getJson('/api/app/support/contact');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message'
                ],
                'data' => [
                    'phone_number',
                    'email'
                ]
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Informations de contact du support récupérées avec succès.');
    }

    /** @test */
    public function it_returns_correct_contact_information(): void
    {
        $response = $this->getJson('/api/app/support/contact');

        $response->assertStatus(200);

        $data = $response->json('data');
        $configPhone = Config::get('support.phone_number');
        $configEmail = Config::get('support.email');

        // Vérifier que les informations correspondent à la configuration
        $this->assertEquals($configPhone, $data['phone_number']);
        $this->assertEquals($configEmail, $data['email']);
    }

    /** @test */
    public function it_returns_valid_phone_number_format(): void
    {
        $response = $this->getJson('/api/app/support/contact');

        $response->assertStatus(200);

        $phoneNumber = $response->json('data.phone_number');

        // Vérifier que le numéro de téléphone est présent et non vide
        $this->assertNotEmpty($phoneNumber);
        $this->assertIsString($phoneNumber);
        
        // Vérifier qu'il contient des caractères typiques d'un numéro de téléphone
        $this->assertMatchesRegularExpression('/^[\+\d\s\-\(\)]+$/', $phoneNumber);
    }

    /** @test */
    public function it_returns_valid_email_format(): void
    {
        $response = $this->getJson('/api/app/support/contact');

        $response->assertStatus(200);

        $email = $response->json('data.email');

        // Vérifier que l'email est présent et valide
        $this->assertNotEmpty($email);
        $this->assertIsString($email);
        $this->assertTrue(filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
    }

    /** @test */
    public function it_returns_valid_json_structure(): void
    {
        $response = $this->getJson('/api/app/support/contact');

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Informations de contact du support récupérées avec succès.'
                ]
            ]);

        // Vérifier que les données obligatoires sont présentes
        $this->assertArrayHasKey('phone_number', $response->json('data'));
        $this->assertArrayHasKey('email', $response->json('data'));
    }
}
