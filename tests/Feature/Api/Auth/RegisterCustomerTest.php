<?php

namespace Tests\Feature\Api\Auth;

use App\Enums\UserRole;
use App\Models\Geography\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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

    public function test_can_register_customer_via_auth_route()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'phone_number' => '699123456',
            'country_id' => 1,
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
        $this->assertEquals('699123456', $user->phone_number);
        $this->assertEquals(1, $user->country_id);
    }

    public function test_requires_country_id()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'phone_number' => '699123456',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_id']);
    }

    public function test_phone_uniqueness_per_country()
    {
        // Create first user with phone in Cameroun
        $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'phone_number' => '699123456',
            'country_id' => 1,
            'password' => 'password123',
        ]);

        // Should fail: same phone + same country
        $response1 = $this->postJson('/api/register/customer', [
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john.smith@example.com',
            'phone_number' => '699123456',
            'country_id' => 1,
            'password' => 'password123',
        ]);

        $response1->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);

        // Should pass: same phone + different country
        $response2 = $this->postJson('/api/register/customer', [
            'first_name' => 'Pierre',
            'last_name' => 'Martin',
            'email' => 'pierre.martin@example.com',
            'phone_number' => '699123456',
            'country_id' => 2,
            'password' => 'password123',
        ]);

        $response2->assertStatus(201);
    }

    public function test_validates_invalid_country_id()
    {
        $response = $this->postJson('/api/register/customer', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'phone_number' => '699123456',
            'country_id' => 999,
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['country_id']);
    }
}
