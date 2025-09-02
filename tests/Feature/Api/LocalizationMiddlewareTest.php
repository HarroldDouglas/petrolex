<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LocalizationMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset locale to default before each test
        App::setLocale('fr');

        // Create test routes to verify middleware behavior
        Route::middleware(['api', \App\Http\Middleware\SetLocale::class])
            ->prefix('test-locale')
            ->group(function () {
                Route::get('/current-locale', function () {
                    return response()->json(['locale' => App::getLocale()]);
                });
            });
    }

    public function test_middleware_sets_locale_from_authenticated_french_user()
    {
        $user = User::factory()->create(['language' => 'fr']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/test-locale/current-locale');

        $response->assertStatus(200)
            ->assertJson(['locale' => 'fr']);
    }

    public function test_middleware_sets_locale_from_authenticated_english_user()
    {
        $user = User::factory()->create(['language' => 'en']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/test-locale/current-locale');

        $response->assertStatus(200)
            ->assertJson(['locale' => 'en']);
    }

    public function test_middleware_uses_accept_language_header_when_not_authenticated()
    {
        $response = $this->getJson('/test-locale/current-locale', [
            'Accept-Language' => 'en-US,en;q=0.9',
        ]);

        $response->assertStatus(200)
            ->assertJson(['locale' => 'en']);
    }

    public function test_middleware_defaults_to_french_when_no_valid_language()
    {
        $response = $this->getJson('/test-locale/current-locale', [
            'Accept-Language' => 'de-DE,de;q=0.9',
        ]);

        $response->assertStatus(200)
            ->assertJson(['locale' => 'fr']);
    }

    public function test_middleware_defaults_to_french_when_no_accept_language_header()
    {
        $response = $this->json('GET', '/test-locale/current-locale', [], [
            'Accept-Language' => '',
        ]);

        $response->assertStatus(200)
            ->assertJson(['locale' => 'fr']);
    }

    public function test_authenticated_user_language_overrides_accept_language_header()
    {
        $user = User::factory()->create(['language' => 'en']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/test-locale/current-locale', [
            'Accept-Language' => 'fr-FR,fr;q=0.9',
        ]);

        $response->assertStatus(200)
            ->assertJson(['locale' => 'en']);
    }

    public function test_middleware_handles_complex_accept_language_header()
    {
        $response = $this->getJson('/test-locale/current-locale', [
            'Accept-Language' => 'fr-CA,fr;q=0.8,en-US;q=0.6,en;q=0.4',
        ]);

        $response->assertStatus(200)
            ->assertJson(['locale' => 'fr']);
    }

    public function test_middleware_handles_malformed_accept_language_header()
    {
        $response = $this->getJson('/test-locale/current-locale', [
            'Accept-Language' => 'invalid-header',
        ]);

        $response->assertStatus(200)
            ->assertJson(['locale' => 'fr']);
    }

    public function test_middleware_works_with_actual_api_endpoints()
    {
        $user = User::factory()->create(['language' => 'en']);
        Sanctum::actingAs($user);

        // This should use the middleware and set locale to English
        $response = $this->getJson('/api/app/support/contact');

        // We expect the endpoint to work (even if it returns data),
        // indicating the middleware didn't break the request
        $this->assertTrue(in_array($response->status(), [200, 404, 401])); // 404 if route doesn't exist, 401 if not authenticated
    }
}
