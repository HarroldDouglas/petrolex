<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Country $country;

    /**
     * Get the complete JSON structure according to Swagger LoginResponse schema
     */
    private function getExpectedLoginResponseStructure(): array
    {
        return [
            '_metadata' => ['success', 'message'],
            'data' => [
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'phone_number',
                    'address',
                    'language',
                    'country' => [
                        'id',
                        'name',
                        'code',
                        'phone_code',
                        'currency',
                        'is_active',
                    ],
                    'email_verified_at',
                    'phone_verified_at',
                    'last_login_at',
                    'roles',
                    'created_at',
                    'updated_at',
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::factory()->create([
            'name' => 'Cameroun',
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'country_id' => $this->country->id,
            'is_active' => true,
        ]);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_can_login_with_email_and_country_code(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure($this->getExpectedLoginResponseStructure())
            ->assertJsonPath('_metadata.success', true);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_can_login_with_email_without_country_code_legacy(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
            // Support legacy sans country_code
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure($this->getExpectedLoginResponseStructure())
            ->assertJsonPath('_metadata.success', true);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_can_login_with_phone_number(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure($this->getExpectedLoginResponseStructure())
            ->assertJsonPath('_metadata.success', true);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_can_login_with_phone_and_country_code(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'country_code' => $this->country->code,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure($this->getExpectedLoginResponseStructure())
            ->assertJsonPath('_metadata.success', true);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_fails_login_with_phone_without_country_code(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'password' => 'password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_fails_login_with_invalid_credentials(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('_metadata.success', false);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_validates_missing_required_fields(): void
    {
        $response = $this->postJson(route('api.login'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password']);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_validates_invalid_country_code(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'password' => 'password',
            'country_code' => 'INVALID',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_validates_empty_fields(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => '',
            'password' => '',
            'country_code' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password'])
            ->assertJsonMissingValidationErrors(['country_code']); // Empty but nullable
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_fails_login_with_invalid_email_format(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Les identifiants fournis sont invalides, vérifiez bien votre email ou téléphone et votre mot de passe.');
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_fails_login_with_nonexistent_user(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('_metadata.success', false);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_fails_login_with_inactive_user(): void
    {
        // Créer un utilisateur inactif
        $inactiveUser = User::factory()->create([
            'password' => bcrypt('password'),
            'country_id' => $this->country->id,
            'is_active' => false, // Utilisateur inactif
        ]);

        $response = $this->postJson(route('api.login'), [
            'login' => $inactiveUser->phone_number, // Utiliser phone pour que ça marche
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('_metadata.success', false);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_validates_country_code_format(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
            'country_code' => 'c', // Trop court
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);

        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
            'country_code' => 'CMR', // Trop long
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);

        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
            'country_code' => '12', // Chiffres au lieu de lettres
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_accepts_form_data_requests(): void
    {
        // Test avec Content-Type form-data
        $response = $this->post(route('api.login'), [
            'login' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_handles_json_validation_errors_correctly(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => '', // Vide
            'password' => '', // Vide
            'country_code' => 'TOOLONG', // Invalide
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password', 'country_code']);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_fails_login_when_email_not_verified(): void
    {
        // Créer un utilisateur avec email non vérifié
        $unverifiedUser = User::factory()->create([
            'password' => bcrypt('password'),
            'country_id' => $this->country->id,
            'is_active' => true,
            'email_verified_at' => null, // Email non vérifié
        ]);

        $response = $this->postJson(route('api.login'), [
            'login' => $unverifiedUser->email,
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('_metadata.success', false);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_validates_phone_number_format_with_country(): void
    {
        // Test avec un numéro qui ne correspond pas au pays
        $response = $this->postJson(route('api.login'), [
            'login' => '1234567890', // Format invalide pour CM
            'password' => 'password',
            'country_code' => 'CM',
        ]);

        $response->assertStatus(401) // Utilisateur non trouvé
            ->assertJsonPath('_metadata.success', false);
    }

    /**
     * @group login
     * @group auth
     */
    #[Test]
    public function it_handles_case_insensitive_country_code(): void
    {
        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'password' => 'password',
            'country_code' => 'cm', // Minuscules
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        $response = $this->postJson(route('api.login'), [
            'login' => $this->user->phone_number,
            'password' => 'password',
            'country_code' => 'CM', // Majuscules
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);
    }
}
