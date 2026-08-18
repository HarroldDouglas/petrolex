<?php

namespace Tests\Unit\Services;

use App\Services\Geography\GoogleMapsLinkParser;
use PHPUnit\Framework\TestCase;

class GoogleMapsLinkParserTest extends TestCase
{
    private GoogleMapsLinkParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new GoogleMapsLinkParser;
    }

    /**
     * @dataProvider validInputs
     */
    public function test_it_extracts_coordinates(string $input, float $lat, float $lng): void
    {
        $result = $this->parser->parse($input);

        $this->assertNotNull($result, "Failed to parse: {$input}");
        $this->assertEqualsWithDelta($lat, $result['latitude'], 0.0000001);
        $this->assertEqualsWithDelta($lng, $result['longitude'], 0.0000001);
    }

    public static function validInputs(): array
    {
        return [
            'place link with pin' => [
                'https://www.google.com/maps/place/Logbessou/@4.0872,9.7845,17z/data=!3m1!4b1!4m6!3m5!1s0x0:0x0!8m2!3d4.0872123!4d9.7845678',
                4.0872123, 9.7845678,
            ],
            'viewport link' => ['https://www.google.com/maps/@4.0511,9.7679,15z', 4.0511, 9.7679],
            'query param' => ['https://maps.google.com/?q=4.0511,9.7679', 4.0511, 9.7679],
            'll param' => ['https://www.google.com/maps?ll=4.0511,9.7679&z=15', 4.0511, 9.7679],
            'bare pair' => ['4.0511, 9.7679', 4.0511, 9.7679],
            'semicolon pair' => ['4.0511;9.7679', 4.0511, 9.7679],
            'negative coordinates' => ['-33.8688, 151.2093', -33.8688, 151.2093],
        ];
    }

    /**
     * The place pin (!3d/!4d) is the address itself; the @ segment is only where the
     * camera sat, so a link carrying both must resolve to the pin.
     */
    public function test_it_prefers_the_place_pin_over_the_viewport_center(): void
    {
        $result = $this->parser->parse(
            'https://www.google.com/maps/place/X/@4.0000000,9.0000000,17z/data=!4m6!3m5!8m2!3d4.9999999!4d9.9999999'
        );

        $this->assertSame(4.9999999, $result['latitude']);
        $this->assertSame(9.9999999, $result['longitude']);
    }

    /**
     * @dataProvider invalidInputs
     */
    public function test_it_returns_null_for_unusable_input(?string $input): void
    {
        $this->assertNull($this->parser->parse($input));
    }

    public static function invalidInputs(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'plain text' => ['pas un lien'],
            'null island' => ['https://www.google.com/maps/@0,0,5z'],
            'latitude out of range' => ['95.0, 9.7679'],
            'longitude out of range' => ['4.0511, 200.0'],
            'maps link without coordinates' => ['https://www.google.com/maps/search/boulangerie'],
        ];
    }
}
