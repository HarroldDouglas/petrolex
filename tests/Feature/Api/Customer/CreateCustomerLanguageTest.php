<?php

namespace Tests\Feature\Api\Customer;

use App\Enums\UserRole;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateCustomerLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // Create all necessary roles
        foreach (UserRole::cases() as $role) {
            Role::create(['name' => $role->value]);
        }

        // Create country for tests
        Country::factory()->create([
            'name' => 'Cameroun',
            'code' => 'CM',
            'phone_code' => '+237',
        ]);
    }

    public function test_can_create_customer_with_french_language()
    {
        $response = $this->postJson('/api/customers', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
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

        $user = User::where('email', 'jean.dupont@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('fr', $user->language);
    }

    public function test_can_create_customer_with_english_language()
    {
        $response = $this->postJson('/api/customers', [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'phone_number' => '677654321',
            'country_code' => 'CM',
            'password' => 'password123',
            'language' => 'en',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'john.smith@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('en', $user->language);
    }

    public function test_customer_creation_defaults_to_french_when_no_language_specified()
    {
        $response = $this->postJson('/api/customers', [
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@example.com',
            'phone_number' => '677787878',
            'country_code' => 'CM',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'marie.martin@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('fr', $user->language);
    }

    public function test_customer_creation_validates_language_enum()
    {
        $response = $this->postJson('/api/customers', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'phone_number' => '677111111',
            'country_code' => 'CM',
            'password' => 'password123',
            'language' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['language']);
    }

    public function test_customer_creation_accepts_null_language()
    {
        $response = $this->postJson('/api/customers', [
            'first_name' => 'Test',
            'last_name' => 'Null',
            'email' => 'testnull@example.com',
            'phone_number' => '677222222',
            'country_code' => 'CM',
            'password' => 'password123',
            'language' => null,
        ]);

        $response->assertStatus(201);

        $user = User::where('email', 'testnull@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('fr', $user->language); // Should default to French
    }

    public function test_otp_email_sent_in_correct_language_french()
    {
        $this->postJson('/api/customers', [
            'first_name' => 'Jean',
            'last_name' => 'Test',
            'email' => 'jean.test@example.com',
            'phone_number' => '677333333',
            'country_code' => 'CM',
            'password' => 'password123',
            'language' => 'fr',
        ]);

        Mail::assertSent(\App\Mail\OtpMail::class, function ($mail) {
            return $mail->userLanguage === 'fr';
        });
    }

    public function test_otp_email_sent_in_correct_language_english()
    {
        $this->postJson('/api/customers', [
            'first_name' => 'John',
            'last_name' => 'Test',
            'email' => 'john.test@example.com',
            'phone_number' => '677444444',
            'country_code' => 'CM',
            'password' => 'password123',
            'language' => 'en',
        ]);

        Mail::assertSent(\App\Mail\OtpMail::class, function ($mail) {
            return $mail->userLanguage === 'en';
        });
    }
}
