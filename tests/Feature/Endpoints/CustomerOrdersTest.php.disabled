<?php

declare(strict_types=1);

namespace Tests\Feature\Endpoints;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CustomerOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private string $authToken;

    protected function setUp(): void
    {
        parent::setUp();

        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'center_manager', 'guard_name' => 'web']);

        $country = \App\Models\Geography\Country::factory()->create([
            'code' => 'CM',
            'phone_code' => '+237',
        ]);

        $this->adminUser = User::factory()->create([
            'country_id' => $country->id,
        ]);
        $this->adminUser->assignRole('admin');

        $response = $this->postJson(route('api.login'), [
            'login' => $this->adminUser->phone_number,
            'password' => 'password',
            'country_code' => 'CM',
        ]);
        $this->authToken = $response->json('data.access_token');
    }

    #[Test]
    public function it_can_list_all_orders_of_a_customer(): void
    {
        $this->markTestSkipped('Route api.customers.orders.index does not exist yet. Need to implement admin endpoint for customer orders.');
    }
}
