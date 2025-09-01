<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private const TEST_EMAIL = 'test@petrolex.com';
    private const TEST_PASSWORD = 'password123';

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => self::TEST_EMAIL,
            'password' => bcrypt(self::TEST_PASSWORD),
        ]);
    }

    public function test_user_can_login_with_email()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => self::TEST_EMAIL,
            'password' => self::TEST_PASSWORD,
            'remember' => false,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_remember_me_functionality_works()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => self::TEST_EMAIL,
            'password' => self::TEST_PASSWORD,
            'remember' => true,
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_page_displays_correctly()
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Bienvenue sur', false)
            ->assertSee('administration', false);
    }

    public function test_login_fails_with_wrong_credentials()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => self::TEST_EMAIL,
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_login_fails_with_nonexistent_user()
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'inexistant@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_validation_errors_for_empty_fields()
    {
        $this->from('/login')->post('/login', [])
            ->assertSessionHasErrors(['email', 'password'])
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
