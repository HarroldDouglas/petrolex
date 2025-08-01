<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class CreateDeliveryTrackingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'delivery_person', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user, ['*']);

        // Load API routes explicitly
        require __DIR__.'/../../../routes/api.php';
    }

    /** @test */
    public function it_can_create_a_delivery_tracking_record(): void
    {
        // Create dependencies for OrderFactory
        \App\Models\Customer::factory()->create();
        \App\Models\DistributionCenter::factory()->create();

        $order = Order::factory()->create();

        $response = $this->postJson(route('api.tracking.delivery.create'), [
            'order_id' => $order->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    'id',
                    'order_id',
                    'status',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.message', 'Suivi de livraison créé avec succès.')
            ->assertJsonPath('data.order_id', $order->id);

        $this->assertDatabaseHas('delivery_trackings', [
            'order_id' => $order->id,
        ]);
    }

    /** @test */
    public function it_returns_a_validation_error_if_order_id_is_missing(): void
    {
        $response = $this->postJson(route('api.tracking.delivery.create'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }

    /** @test */
    public function it_returns_a_validation_error_if_order_id_does_not_exist(): void
    {
        $response = $this->postJson(route('api.tracking.delivery.create'), [
            'order_id' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['order_id']);
    }
}
