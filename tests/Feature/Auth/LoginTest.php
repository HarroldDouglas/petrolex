<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithSession;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use InteractsWithSession;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_admin_can_login_with_email()
    {
        $email = $_ENV['ADMIN_EMAIL'] ?? 'admin@petrolex.com';
        $password = $_ENV['ADMIN_PASSWORD'] ?? 'password';

        // Assuming a user with this email and password exists in the database
        // For a real application, you might need to seed the database with this user
        // or ensure your test setup creates it.
        // For this test, we'll assume the user exists or is created by a seeder.

        $this->withoutCsrfProtection();
        $response = $this->post('/login', [
            'login' => $email,
            'password' => $password,
            'remember' => false
        ]);

        $response->assertRedirect('/dashboard'); // Or your actual dashboard route
        $this->assertAuthenticated(); // Assert any authenticated user
    }

    public function test_admin_can_login_with_phone()
    {
        $phone = $_ENV['ADMIN_PHONE'] ?? '237699999999';
        $password = $_ENV['ADMIN_PASSWORD'] ?? 'password';

        $this->withoutCsrfProtection();
        $response = $this->post('/login', [
            'login' => $phone,
            'password' => $password
        ]);

        $response->assertRedirect('/dashboard'); // Or your actual dashboard route
        $this->assertAuthenticated(); // Assert any authenticated user
    }

    public function test_login_page_displays_correctly()
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Bienvenue sur l\'administration')
            ->assertSee('Email ou Téléphone')
            ->assertSee('Mot de passe')
            ->assertSee('Souvenez-vous de moi')
            ->assertSee('Mot de passe oublié ?');
            // ->assertSee('logo'); // Uncomment if 'logo' is a visible text or alt attribute
    }

    public function test_login_fails_with_wrong_credentials()
    {
        $email = $_ENV['ADMIN_EMAIL'] ?? 'admin@petrolex.com';

        $this->withoutCsrfProtection();
        $response = $this->post('/login', [
            'login' => $email,
            'password' => 'wrong-password'
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['login']) // Or 'email' depending on your config
            ->assertSessionHas('error', 'Ces identifiants ne correspondent pas'); // Your exact message

        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_user()
    {
        $this->withoutCsrfProtection();
        $response = $this->post('/login', [
            'login' => 'inexistant@example.com',
            'password' => 'password123'
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors()
            ->assertSee('Ces identifiants ne correspondent pas'); // After redirect
    }

    public function test_validation_errors_for_empty_fields()
    {
        $this->withoutCsrfProtection();
        $this->post('/login', [])
            ->assertSessionHasErrors(['login', 'password'])
            ->assertRedirect('/login');

        // You can test exact messages
        $this->followingRedirects()
             ->post('/login', [])
             ->assertSee('Le champ email est obligatoire')
             ->assertSee('Le champ mot de passe est obligatoire');
    }

    public function test_remember_me_functionality_works()
    {
        $email = $_ENV['ADMIN_EMAIL'] ?? 'admin@petrolex.com';
        $password = $_ENV['ADMIN_PASSWORD'] ?? 'password';

        $this->withoutCsrfProtection();
        $response = $this->post('/login', [
            'login' => $email,
            'password' => $password,
            'remember' => true
        ]);

        $this->assertAuthenticated();
        // Laravel automatically creates the remember_token
    }
}