<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\DeliveryPerson;
use App\Models\DistributionCenter;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class DeliveryPersonOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private DeliveryPerson $deliveryPerson;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles for testing
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'delivery_person', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        // Create an admin user and authenticate using Sanctum
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');
        Sanctum::actingAs($this->adminUser, ['*']);

        // Create a delivery person
        $this->deliveryPerson = DeliveryPerson::factory()->create();

        // Create necessary dependencies for OrderFactory
        Customer::factory()->create();
        DistributionCenter::factory()->create();
        CustomerDeliveryAddress::factory()->create();
    }

    /** @test */
    public function it_can_retrieve_a_list_of_orders_for_a_delivery_person(): void
    {
        // Create orders for the delivery person
        Order::factory()->count(5)->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'status' => OrderStatus::CONFIRMED(),
        ]);
        Order::factory()->count(3)->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'status' => OrderStatus::DELIVERED(),
        ]);

        $response = $this->getJson(route('api.delivery-persons.orders', ['deliveryPerson' => $this->deliveryPerson->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                    'pagination' => [
                        'total',
                        'current_page_total',
                        'per_page',
                        'current_page',
                        'total_pages',
                        'next_page_url',
                        'prev_page_url',
                    ],
                ],
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'delivery_type',
                        'subtotal',
                        'delivery_fee',
                        'total_amount',
                        'order_date',
                        'delivery_date',
                        'status',
                        'payment' => [
                            'id',
                            'status',
                            'date',
                            'reference',
                            'method',
                        ],
                        'delivery_address' => [
                            'id',
                            'name',
                            'contact_name',
                            'email',
                            'city',
                            'country',
                            'neighborhood',
                            'address_precision',
                            'latitude',
                            'longitude',
                        ],
                    ],
                ],
            ])
            ->assertJsonPath('_metadata.success', true)
            ->assertJsonPath('_metadata.pagination.total', 8)
            ->assertJsonPath('_metadata.pagination.current_page_total', 8);
    }

    /** @test */
    public function it_can_filter_orders_by_status(): void
    {
        Order::factory()->count(5)->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'status' => OrderStatus::CONFIRMED(),
        ]);
        Order::factory()->count(3)->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'status' => OrderStatus::DELIVERED(),
        ]);

        $response = $this->getJson(route('api.delivery-persons.orders', [
            'deliveryPerson' => $this->deliveryPerson->id,
            'status' => OrderStatus::CONFIRMED()->value,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.pagination.total', 5)
            ->assertJsonPath('_metadata.pagination.current_page_total', 5)
            ->assertJsonPath('data.0.status', OrderStatus::CONFIRMED()->value);
    }

    /** @test */
    public function it_can_filter_orders_by_order_number(): void
    {
        Order::factory()->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'order_number' => 'TEST-ORDER-123',
        ]);
        Order::factory()->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'order_number' => 'ANOTHER-ORDER-456',
        ]);

        $response = $this->getJson(route('api.delivery-persons.orders', [
            'deliveryPerson' => $this->deliveryPerson->id,
            'order_number' => 'TEST-ORDER-123',
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.pagination.total', 1)
            ->assertJsonPath('data.0.order_number', 'TEST-ORDER-123');
    }

    /** @test */
    public function it_can_filter_orders_by_delivery_type(): void
    {
        Order::factory()->count(2)->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'delivery_type' => DeliveryType::NORMAL(),
        ]);
        Order::factory()->count(3)->create([
            'delivery_person_id' => $this->deliveryPerson->id,
            'delivery_type' => DeliveryType::FAST(),
        ]);

        $response = $this->getJson(route('api.delivery-persons.orders', [
            'deliveryPerson' => $this->deliveryPerson->id,
            'delivery_type' => DeliveryType::FAST()->value,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.pagination.total', 3)
            ->assertJsonPath('data.0.delivery_type', DeliveryType::FAST()->value);
    }

    /** @test */
    public function it_handles_pagination(): void
    {
        Order::factory()->count(20)->create([
            'delivery_person_id' => $this->deliveryPerson->id,
        ]);

        $response = $this->getJson(route('api.delivery-persons.orders', [
            'deliveryPerson' => $this->deliveryPerson->id,
            'per_page' => 5,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('_metadata.pagination.total', 20)
            ->assertJsonPath('_metadata.pagination.per_page', 5)
            ->assertJsonPath('_metadata.pagination.current_page_total', 5);
    }

    /** @test */
    public function it_returns_404_if_delivery_person_not_found(): void
    {
        $response = $this->getJson(route('api.delivery-persons.orders', ['deliveryPerson' => 99999])); // Non-existent ID

        $response->assertStatus(404)
            ->assertJsonPath('message', 'No query results for model [App\\Models\\DeliveryPerson] 99999');
    }
}
