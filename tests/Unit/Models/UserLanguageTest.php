<?php

namespace Tests\Unit\Models;

use App\Enums\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_language_defaults_to_french()
    {
        $user = User::factory()->create();

        // The factory may set a random language, but let's check the fallback
        $userWithoutLanguage = new User;
        $userWithoutLanguage->setRawAttributes(['language' => null]);

        $this->assertEquals('fr', $userWithoutLanguage->language);
    }

    public function test_user_language_can_be_set_to_english()
    {
        $user = User::factory()->create(['language' => 'en']);

        $this->assertEquals('en', $user->language);
    }

    public function test_user_language_can_be_set_to_french_explicitly()
    {
        $user = User::factory()->create(['language' => 'fr']);

        $this->assertEquals('fr', $user->language);
    }

    public function test_user_language_attribute_fallback_works()
    {
        $user = new User;

        // Simulate database returning null
        $user->setRawAttributes(['language' => null]);

        $this->assertEquals(Language::default(), $user->language);
    }

    public function test_user_language_is_fillable()
    {
        $fillable = (new User)->getFillable();

        $this->assertContains('language', $fillable);
    }

    public function test_user_language_is_cast_as_string()
    {
        $casts = (new User)->getCasts();

        $this->assertArrayHasKey('language', $casts);
        $this->assertEquals('string', $casts['language']);
    }
}
