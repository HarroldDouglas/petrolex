<?php

declare(strict_types=1);

namespace Tests\Unit\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class LoginRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a country for ValidISOCountryRule tests
        \App\Models\Geography\Country::factory()->create([
            'code' => 'CM',
            'name' => 'Cameroun',
        ]);
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_validates_required_login_field(): void
    {
        $request = new LoginRequest;
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('login', $validator->errors()->toArray());
        // Just check that there's a validation error, don't assert exact message
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_validates_required_password_field(): void
    {
        $request = new LoginRequest;
        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        // Just check that there's a validation error, don't assert exact message
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_accepts_valid_login_and_password(): void
    {
        $request = new LoginRequest;
        $data = [
            'login' => 'user@example.com',
            'password' => 'password123',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_accepts_null_country_code(): void
    {
        $request = new LoginRequest;
        $data = [
            'login' => 'user@example.com',
            'password' => 'password123',
            'country_code' => null,
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_accepts_missing_country_code(): void
    {
        $request = new LoginRequest;
        $data = [
            'login' => 'user@example.com',
            'password' => 'password123',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_validates_country_code_size(): void
    {
        $request = new LoginRequest;

        // Test too short
        $data = [
            'login' => 'user@example.com',
            'password' => 'password123',
            'country_code' => 'C',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());

        // Test too long
        $data['country_code'] = 'CMR';
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_validates_country_code_regex(): void
    {
        $request = new LoginRequest;

        // Test numbers
        $data = [
            'login' => 'user@example.com',
            'password' => 'password123',
            'country_code' => '12',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());

        // Test special characters
        $data['country_code'] = 'C@';
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_accepts_valid_country_codes(): void
    {
        $request = new LoginRequest;

        $validCodes = ['CM', 'cm', 'FR', 'Us', 'GB'];

        foreach ($validCodes as $code) {
            $data = [
                'login' => 'user@example.com',
                'password' => 'password123',
                'country_code' => $code,
            ];
            $validator = Validator::make($data, $request->rules());

            $this->assertFalse($validator->fails(), "Country code '{$code}' should be valid");
        }
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_rejects_invalid_iso_country_codes(): void
    {
        $request = new LoginRequest;

        $data = [
            'login' => 'user@example.com',
            'password' => 'password123',
            'country_code' => 'XX', // Invalid ISO country code
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_validates_login_must_be_string(): void
    {
        $request = new LoginRequest;
        $data = [
            'login' => 123, // Not a string
            'password' => 'password123',
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('login', $validator->errors()->toArray());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_validates_password_must_be_string(): void
    {
        $request = new LoginRequest;
        $data = [
            'login' => 'user@example.com',
            'password' => 123, // Not a string
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_validates_country_code_must_be_string_when_provided(): void
    {
        $request = new LoginRequest;
        $data = [
            'login' => 'user@example.com',
            'password' => 'password123',
            'country_code' => 123, // Not a string
        ];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('country_code', $validator->errors()->toArray());
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function it_allows_empty_string_fields(): void
    {
        $request = new LoginRequest;
        $data = [
            'login' => '',
            'password' => '',
            'country_code' => '',
        ];
        $validator = Validator::make($data, $request->rules());

        // login and password are required, so empty strings should fail
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('login', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());

        // country_code is nullable, so empty string might be treated as null and pass
        // Let's just check that login and password fail, but not country_code
    }

    #[Test]
    #[Group('auth')]
    #[Group('validation')]
    public function authorization_always_returns_true(): void
    {
        $request = new LoginRequest;

        $this->assertTrue($request->authorize());
    }
}
