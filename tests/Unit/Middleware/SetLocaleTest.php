<?php

namespace Tests\Unit\Middleware;

use App\Enums\Language;
use App\Http\Middleware\SetLocale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SetLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected SetLocale $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SetLocale;

        // Reset locale to default before each test
        App::setLocale('en'); // Laravel default
    }

    public function test_sets_locale_from_authenticated_user_french()
    {
        $user = User::factory()->create(['language' => 'fr']);
        Auth::login($user);

        $request = Request::create('/test');

        $this->middleware->handle($request, function () {
            $this->assertEquals('fr', App::getLocale());

            return response('OK');
        });
    }

    public function test_sets_locale_from_authenticated_user_english()
    {
        $user = User::factory()->create(['language' => 'en']);
        Auth::login($user);

        $request = Request::create('/test');

        $this->middleware->handle($request, function () {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    public function test_sets_locale_from_accept_language_header_when_not_authenticated()
    {
        $request = Request::create('/test');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');

        $this->middleware->handle($request, function () {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }

    public function test_defaults_to_french_when_no_valid_language_found()
    {
        $request = Request::create('/test');
        $request->headers->set('Accept-Language', 'de-DE,de;q=0.9');

        $this->middleware->handle($request, function () {
            $this->assertEquals('fr', App::getLocale());

            return response('OK');
        });
    }

    public function test_defaults_to_french_when_no_accept_language_header()
    {
        Auth::logout(); // Ensure no user is authenticated
        $request = Request::create('/test');
        // Explicitly remove any Accept-Language header
        $request->headers->remove('Accept-Language');

        $this->middleware->handle($request, function () {
            $this->assertEquals('fr', App::getLocale());

            return response('OK');
        });
    }

    public function test_handles_complex_accept_language_header()
    {
        $request = Request::create('/test');
        $request->headers->set('Accept-Language', 'fr-CA,fr;q=0.8,en-US;q=0.6,en;q=0.4');

        $this->middleware->handle($request, function () {
            $this->assertEquals('fr', App::getLocale());

            return response('OK');
        });
    }

    public function test_user_language_takes_precedence_over_header()
    {
        $user = User::factory()->create(['language' => 'en']);
        Auth::login($user);

        $request = Request::create('/test');
        $request->headers->set('Accept-Language', 'fr-FR,fr;q=0.9');

        $this->middleware->handle($request, function () {
            $this->assertEquals('en', App::getLocale());

            return response('OK');
        });
    }
}
