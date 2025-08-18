<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class UpdatePasswordTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $oldPassword = 'password';

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);
    }

    /** @test */
    public function it_can_update_authenticated_user_password(): void
    {
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);

        $this->actingAs($this->user);

        $newPassword = 'new_strong_password';

        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ]);

        $response->assertOk();
        $response->assertJsonPath('_metadata.success', true);

        $this->assertTrue(Hash::check($newPassword, $this->user->fresh()->password));
    }

    /** @test */
    public function it_cannot_update_password_with_incorrect_old_password(): void
    {
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);

        $this->actingAs($this->user);

        $newPassword = 'new_strong_password';

        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => 'wrong_password',
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
        ]);

        $response->assertStatus(422); // Validation error from custom rule
        $response->assertJsonValidationErrors(['old_password']);
    }

    /** @test */
    public function it_cannot_update_password_with_invalid_new_password(): void
    {
        $this->user = User::factory()->create([
            'password' => Hash::make($this->oldPassword),
        ]);

        $this->actingAs($this->user);

        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => '123',
            'new_password_confirmation' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['new_password']);

        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => 'new_strong_password',
            'new_password_confirmation' => 'mismatch',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_password(): void
    {
        $response = $this->patchJson(route('api.password.update'), [
            'old_password' => $this->oldPassword,
            'new_password' => 'new_strong_password',
            'new_password_confirmation' => 'new_strong_password',
        ]);

        $response->assertStatus(401);
    }
}
