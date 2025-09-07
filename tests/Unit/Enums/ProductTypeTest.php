<?php

namespace Tests\Unit\Enums;

use App\Enums\ProductType;
use Tests\TestCase;

class ProductTypeTest extends TestCase
{
    public function test_bottle_label_for_mobile_returns_correct_french_translation()
    {
        $productType = ProductType::BOTTLE();

        $label = $productType->labelForMobile('fr');

        $this->assertEquals('Bouteilles à gaz domestiques', $label);
    }

    public function test_bottle_label_for_mobile_returns_correct_english_translation()
    {
        $productType = ProductType::BOTTLE();

        $label = $productType->labelForMobile('en');

        $this->assertEquals('Domestic Gas Bottles', $label);
    }

    public function test_accessory_label_for_mobile_returns_correct_french_translation()
    {
        $productType = ProductType::ACCESSORY();

        $label = $productType->labelForMobile('fr');

        $this->assertEquals('Accessoires de sécurité et distributions', $label);
    }

    public function test_accessory_label_for_mobile_returns_correct_english_translation()
    {
        $productType = ProductType::ACCESSORY();

        $label = $productType->labelForMobile('en');

        $this->assertEquals('Safety and Distribution Accessories', $label);
    }

    public function test_label_for_mobile_defaults_to_french_for_unknown_locale()
    {
        $productType = ProductType::BOTTLE();

        $label = $productType->labelForMobile('unknown');

        $this->assertEquals('Bouteilles à gaz domestiques', $label);
    }

    public function test_label_for_mobile_uses_app_locale_when_no_locale_provided()
    {
        app()->setLocale('en');
        $productType = ProductType::ACCESSORY();

        $label = $productType->labelForMobile();

        $this->assertEquals('Safety and Distribution Accessories', $label);

        app()->setLocale('fr');
        $label = $productType->labelForMobile();

        $this->assertEquals('Accessoires de sécurité et distributions', $label);
    }
}
