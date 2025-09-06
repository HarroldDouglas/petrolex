<?php

namespace Tests\Feature\Api\Delivery;

use App\Enums\DeliveryType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetDeliveryTypesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_delivery_types_with_fees()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/delivery-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    '*' => [
                        'value',
                        'label',
                        'fee',
                    ],
                ],
            ])
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Delivery types retrieved successfully.',
                ],
            ]);

        $data = $response->json('data');
        $this->assertCount(2, $data);

        // Check that both delivery types are present with correct fees
        $normalType = collect($data)->firstWhere('value', 'normal');
        $fastType = collect($data)->firstWhere('value', 'fast');

        $this->assertNotNull($normalType);
        $this->assertNotNull($fastType);

        $this->assertEquals('Standard', $normalType['label']);
        $this->assertEquals(500, $normalType['fee']);

        $this->assertEquals('Express', $fastType['label']);
        $this->assertEquals(1000, $fastType['fee']);
    }

    public function test_requires_authentication()
    {
        $response = $this->getJson('/api/delivery-types');

        $response->assertStatus(401);
    }

    public function test_delivery_types_include_all_enum_cases()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/delivery-types');

        $response->assertStatus(200);

        $data = $response->json('data');
        $returnedValues = collect($data)->pluck('value')->sort()->values();
        $enumValues = collect(DeliveryType::cases())->pluck('value')->sort()->values();

        $this->assertEquals($enumValues, $returnedValues);
    }

    public function test_fees_match_enum_definitions()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/delivery-types');

        $response->assertStatus(200);

        $data = $response->json('data');
        
        foreach ($data as $deliveryType) {
            $enumCase = DeliveryType::from($deliveryType['value']);
            $this->assertEquals($enumCase->fee(), $deliveryType['fee']);
        }
    }
}