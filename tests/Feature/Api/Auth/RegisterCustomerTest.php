<?php

namespace Tests\Feature\Api\Auth;

use App\Enums\UserRole;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegisterCustomerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        foreach (UserRole::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        Country::factory()->create([
            'id' => 1,
            'name' => 'Cameroun',
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        Country::factory()->create([
            'id' => 2,
            'name' => 'France',
            'code' => 'FR',
            'phone_code' => '+33',
        ]);
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_can_register_customer_via_auth_route()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'phone_number' => '677123456',
            'country_code' => 'CM',
            'password' => 'password123',
            'language' => 'fr',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                '_metadata' => ['success', 'message'],
                'data' => ['identifier'],
            ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('677123456', $user->phone_number);
        $this->assertEquals(1, $user->country_id);
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_requires_country_code()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'phone_number' => '677123456',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_phone_uniqueness_per_country()
    {
        // Create first user with phone in Cameroun
        $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test1@example.com',
            'phone_number' => '677123456',
            'country_code' => 'CM',
            'password' => 'password123',
        ]);

        // Should fail: same phone + same country
        $response1 = $this->postJson('/api/register/customer', [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'test2@example.com',
            'phone_number' => '677123456',
            'country_code' => 'CM',
            'password' => 'password123',
        ]);

        $response1->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        // Should pass: same phone + different country
        $response2 = $this->postJson('/api/register/customer', [
            'first_name' => 'Pierre',
            'last_name' => 'Martin',
            'email' => 'test3@example.com',
            'phone_number' => '0123456789', // Valid FR format
            'country_code' => 'FR',
            'password' => 'password123',
        ]);

        $response2->assertStatus(201);
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_validates_invalid_country_code()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'phone_number' => '677123456',
            'country_code' => 'XX',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_code']);
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_validates_missing_required_fields()
    {
        $response = $this->postJson('/api/register/customer', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'country_code',
                'password',
            ]);
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_validates_field_formats()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => '',
            'last_name' => '',
            'email' => 'invalid-email',
            'phone_number' => '123', // too short
            'country_code' => 'CM',
            'password' => '123', // too short
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'password',
            ])
            ->assertJsonMissingValidationErrors(['country_code']); // Should be valid
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_can_register_with_supported_country_in_db()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test.cm@example.com',
            'phone_number' => '677123456',
            'country_code' => 'CM',
            'password' => 'password123',
            'language' => 'fr',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'test.cm@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('677123456', $user->phone_number);
        $this->assertEquals(1, $user->country_id); // Has country_id because CM is in our DB
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_can_register_with_us_country_not_in_db()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'test.us@example.com',
            'phone_number' => '5551234567',
            'country_code' => 'US',
            'password' => 'password123',
            'language' => 'en',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'test.us@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('5551234567', $user->phone_number);
        $this->assertNull($user->country_id); // No country_id because US is not in our delivery DB
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_validates_phone_format_for_cameroon()
    {
        // Invalid CM phone (should start with 6)
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test@example.com',
            'phone_number' => '577123456', // Invalid: starts with 5
            'country_code' => 'CM',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        // Test too short (8 digits)
        $response2 = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test2@example.com',
            'phone_number' => '67712345', // Invalid: only 8 digits
            'country_code' => 'CM',
            'password' => 'password123',
        ]);

        $response2->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        // Test valid CM phone (9 digits starting with 6)
        $response3 = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test3@example.com',
            'phone_number' => '677123456', // Valid: 9 digits starting with 6
            'country_code' => 'CM',
            'password' => 'password123',
        ]);

        $response3->assertStatus(201); // Should succeed
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_accepts_any_valid_iso_country_code()
    {
        $countries = ['FR', 'GB', 'DE', 'NG', 'CA', 'JP', 'AU'];

        foreach ($countries as $countryCode) {
            $response = $this->postJson('/api/register/customer', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => "test.{$countryCode}@example.com",
                'phone_number' => '1234567890', // Default format
                'country_code' => $countryCode,
                'password' => 'password123',
            ]);

            $response->assertStatus(201);
        }
    }

    #[Test]
    #[Group('register')]
    #[Group('auth')]
    public function test_phone_uniqueness_works_across_countries()
    {
        // Create user in CM
        $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'test1@example.com',
            'phone_number' => '677123456',
            'country_code' => 'CM',
            'password' => 'password123',
        ]);

        // Different phone in different country should be allowed
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'test2@example.com',
            'phone_number' => '5551234567', // Valid US format
            'country_code' => 'US',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
    }
}
