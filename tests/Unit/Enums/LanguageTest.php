<?php

namespace Tests\Unit\Enums;

use App\Enums\Language;
use PHPUnit\Framework\TestCase;

class LanguageTest extends TestCase
{
    public function test_language_enum_has_correct_values()
    {
        $this->assertEquals(['fr' => 'FRENCH', 'en' => 'ENGLISH'], Language::toArray());
    }

    public function test_language_enum_get_values_method_returns_array()
    {
        $values = Language::getValues();
        $this->assertIsArray($values);
        $this->assertCount(2, $values);
        $this->assertContains('fr', $values);
        $this->assertContains('en', $values);
    }

    public function test_default_language_is_french()
    {
        $this->assertEquals('fr', Language::default());
    }

    public function test_french_constant_returns_correct_value()
    {
        $this->assertEquals('fr', Language::FRENCH()->value);
    }

    public function test_english_constant_returns_correct_value()
    {
        $this->assertEquals('en', Language::ENGLISH()->value);
    }

    public function test_language_enum_equality()
    {
        $french1 = Language::FRENCH();
        $french2 = Language::FRENCH();

        $this->assertTrue($french1->equals($french2));
        $this->assertFalse($french1->equals(Language::ENGLISH()));
    }
}
