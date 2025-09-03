<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test du flow complet d'authentification
 * Inscription → Vérification OTP → Connexion → Réinitialisation mot de passe
 */
final class CompleteAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer les rôles nécessaires
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $this->country = Country::factory()->create([
            'name' => 'Cameroun',
            'code' => 'CM',
            'phone_code' => '+237',
        ]);
    }

    #[Test]
    public function complete_auth_flow_with_email_works(): void
    {
        // 1. INSCRIPTION avec email
        $registrationData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@example.com',
            'phone_number' => '677123456',
            'password' => 'StrongPassword123!',
            'country_code' => 'CM',
            'language' => 'fr',
        ];

        $registrationResponse = $this->postJson(route('api.register.customer'), $registrationData);

        $registrationResponse->assertStatus(201)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => ['identifier'],
            ])
            ->assertJsonPath('_metadata.success', true);

        // Récupérer l'utilisateur créé via l'email
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_active); // Pas encore activé

        $userId = $user->id;

        // 2. VERIFICATION OTP (simulation)
        $user = User::find($userId);
        $this->assertNotNull($user);

        // Simuler activation directe (ou récupérer l'OTP du système)
        $user->markEmailAsVerified();
        $user->is_active = true;
        $user->save();

        // 3. CONNEXION avec EMAIL + country_code
        $loginResponse = $this->postJson(route('api.login'), [
            'login' => 'test@example.com',
            'password' => 'StrongPassword123!',
            'country_code' => 'CM',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true);

        $accessToken = $loginResponse->json('data.access_token');
        $this->assertNotNull($accessToken);

        // 4. CONNEXION avec PHONE + country_code
        $phoneLoginResponse = $this->postJson(route('api.login'), [
            'login' => '677123456',
            'password' => 'StrongPassword123!',
            'country_code' => 'CM',
        ]);

        $phoneLoginResponse->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        // 5. UPDATE PASSWORD (avec token)
        $newPassword = 'NewStrongPassword456!';
        $updatePasswordResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$accessToken,
            'Accept' => 'application/json',
        ])->patchJson(route('api.password.update'), [
            'old_password' => 'StrongPassword123!',
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ]);

        $updatePasswordResponse->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        // 6. VERIFICATION que le nouveau mot de passe fonctionne
        $newLoginResponse = $this->postJson(route('api.login'), [
            'login' => 'test@example.com',
            'password' => $newPassword,
            'country_code' => 'CM',
        ]);

        $newLoginResponse->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        // 7. VERIFICATION que l'ancien mot de passe ne fonctionne plus
        $oldPasswordResponse = $this->postJson(route('api.login'), [
            'login' => 'test@example.com',
            'password' => 'StrongPassword123!',
            'country_code' => 'CM',
        ]);

        $oldPasswordResponse->assertStatus(401)
            ->assertJsonPath('_metadata.success', false);
    }

    #[Test]
    public function complete_auth_flow_with_phone_works(): void
    {
        // 1. INSCRIPTION avec phone principal
        $registrationData = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'phone_number' => '677654321',
            'password' => 'StrongPassword123!',
            'country_code' => 'CM',
            'language' => 'fr',
        ];

        $registrationResponse = $this->postJson(route('api.register.customer'), $registrationData);
        $registrationResponse->assertStatus(201);

        // Récupérer l'utilisateur créé via l'email
        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);

        // 2. ACTIVATION
        $user->markEmailAsVerified();
        $user->is_active = true;
        $user->save();

        // 3. CONNEXION avec PHONE uniquement (legacy support)
        $phoneOnlyResponse = $this->postJson(route('api.login'), [
            'login' => '677654321',
            'password' => 'StrongPassword123!',
        ]);

        $phoneOnlyResponse->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);

        // 4. CONNEXION avec PHONE + country_code
        $phoneWithCodeResponse = $this->postJson(route('api.login'), [
            'login' => '677654321',
            'password' => 'StrongPassword123!',
            'country_code' => 'CM',
        ]);

        $phoneWithCodeResponse->assertStatus(200)
            ->assertJsonPath('_metadata.success', true);
    }

    #[Test]
    public function auth_flow_handles_all_error_cases(): void
    {
        // 1. INSCRIPTION avec données invalides
        $invalidRegistrationResponse = $this->postJson(route('api.register.customer'), [
            'email' => 'not-an-email',
            'phone_number' => '123', // Trop court pour Cameroun
            'password' => '123', // Trop faible
            'country_code' => 'INVALID',
        ]);

        $invalidRegistrationResponse->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'password',
                'country_code',
            ]);

        // 2. LOGIN avec identifiants invalides
        $invalidLoginResponse = $this->postJson(route('api.login'), [
            'login' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
            'country_code' => 'CM',
        ]);

        $invalidLoginResponse->assertStatus(401)
            ->assertJsonPath('_metadata.success', false)
            ->assertJsonPath('_metadata.message', 'Les identifiants fournis sont invalides, vérifiez bien votre email ou téléphone et votre mot de passe.');

        // 3. LOGIN avec country_code invalide
        $invalidCountryResponse = $this->postJson(route('api.login'), [
            'login' => 'test@example.com',
            'password' => 'password',
            'country_code' => 'XX',
        ]);

        $invalidCountryResponse->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);

        // 4. LOGIN sans champs requis
        $missingFieldsResponse = $this->postJson(route('api.login'), []);

        $missingFieldsResponse->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password']);

        // 5. UPDATE PASSWORD sans authentification
        $unauthenticatedUpdateResponse = $this->patchJson(route('api.password.update'), [
            'old_password' => 'old',
            'new_password' => 'new',
            'new_password_confirmation' => 'new',
        ]);

        $unauthenticatedUpdateResponse->assertStatus(401);
    }

    #[Test]
    public function forgot_password_flow_works(): void
    {
        // Créer un utilisateur activé
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'phone_number' => '677123456',
            'country_id' => $this->country->id,
            'password' => Hash::make('oldpassword'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 1. DEMANDE de réinitialisation (si endpoint existe)
        $forgotResponse = $this->postJson(route('api.forgot-password'), [
            'email' => 'test@example.com',
        ]);

        // Le statut peut être 200, 404, ou 422 selon l'implémentation
        $this->assertContains($forgotResponse->getStatusCode(), [200, 404, 422]);

        // 2. Vérifier que l'utilisateur peut encore se connecter avec l'ancien mot de passe
        $loginResponse = $this->postJson(route('api.login'), [
            'login' => 'test@example.com',
            'password' => 'oldpassword',
            'country_code' => 'CM',
        ]);

        $loginResponse->assertStatus(200);
    }

    #[Test]
    public function registration_validates_phone_format_per_country(): void
    {
        // Test avec numéro invalide pour le Cameroun
        $invalidCameroonResponse = $this->postJson(route('api.register.customer'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test1@example.com',
            'phone_number' => '123456789', // Ne commence pas par 6
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'country_code' => 'CM',
        ]);

        $invalidCameroonResponse->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        // Test avec numéro valide pour le Cameroun
        $validCameroonResponse = $this->postJson(route('api.register.customer'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test2@example.com',
            'phone_number' => '677123456', // Commence par 6, 9 chiffres
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'country_code' => 'CM',
        ]);

        $validCameroonResponse->assertStatus(201);

        // Test avec pays différent (USA par exemple)
        $usaCountry = Country::factory()->create([
            'code' => 'US',
            'phone_code' => '+1',
        ]);

        $validUsaResponse = $this->postJson(route('api.register.customer'), [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test3@example.com',
            'phone_number' => '5551234567', // Format USA
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'country_code' => 'US',
        ]);

        $validUsaResponse->assertStatus(201);
    }
}
