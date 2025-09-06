<?php

namespace Tests\Feature\Api\App;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GetAdvertisingBannersControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_advertising_banners()
    {
        Config::set('advertising.banners', [
            [
                'id' => 1,
                'title' => 'Test Banner 1',
                'description' => 'Test description 1',
                'image_url' => 'https://example.com/banner1.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 1,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ],
            [
                'id' => 2,
                'title' => 'Test Banner 2',
                'description' => 'Test description 2',
                'image_url' => 'https://example.com/banner2.jpg',
                'action_url' => 'https://example.com/action',
                'is_active' => true,
                'priority' => 2,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ],
        ]);

        $response = $this->getJson('/api/app/advertising/banners');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    'banners' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'image_url',
                            'action_url',
                            'is_active',
                            'priority',
                            'start_date',
                            'end_date',
                        ],
                    ],
                    'total',
                ],
            ])
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Bannières publicitaires récupérées avec succès.',
                ],
                'data' => [
                    'total' => 2,
                ],
            ]);

        $banners = $response->json('data.banners');
        $this->assertCount(2, $banners);
        $this->assertEquals('Test Banner 1', $banners[0]['title']);
        $this->assertEquals('Test Banner 2', $banners[1]['title']);
    }

    public function test_filters_inactive_banners()
    {
        Config::set('advertising.banners', [
            [
                'id' => 1,
                'title' => 'Active Banner',
                'description' => 'This banner is active',
                'image_url' => 'https://example.com/active.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 1,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ],
            [
                'id' => 2,
                'title' => 'Inactive Banner',
                'description' => 'This banner is inactive',
                'image_url' => 'https://example.com/inactive.jpg',
                'action_url' => null,
                'is_active' => false,
                'priority' => 2,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ],
        ]);

        $response = $this->getJson('/api/app/advertising/banners');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(1, $data['total']);
        $this->assertCount(1, $data['banners']);
        $this->assertEquals('Active Banner', $data['banners'][0]['title']);
    }

    public function test_filters_banners_by_date_range()
    {
        $pastDate = now()->subDays(10)->format('Y-m-d');
        $futureDate = now()->addDays(10)->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        Config::set('advertising.banners', [
            [
                'id' => 1,
                'title' => 'Current Banner',
                'description' => 'This banner should be shown',
                'image_url' => 'https://example.com/current.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 1,
                'start_date' => $pastDate,
                'end_date' => $futureDate,
            ],
            [
                'id' => 2,
                'title' => 'Expired Banner',
                'description' => 'This banner is expired',
                'image_url' => 'https://example.com/expired.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 2,
                'start_date' => $pastDate,
                'end_date' => $yesterday,
            ],
            [
                'id' => 3,
                'title' => 'Future Banner',
                'description' => 'This banner is not yet active',
                'image_url' => 'https://example.com/future.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 3,
                'start_date' => $tomorrow,
                'end_date' => $futureDate,
            ],
        ]);

        $response = $this->getJson('/api/app/advertising/banners');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(1, $data['total']);
        $this->assertCount(1, $data['banners']);
        $this->assertEquals('Current Banner', $data['banners'][0]['title']);
    }

    public function test_sorts_banners_by_priority()
    {
        Config::set('advertising.banners', [
            [
                'id' => 1,
                'title' => 'Low Priority Banner',
                'description' => 'Priority 5',
                'image_url' => 'https://example.com/low.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 5,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ],
            [
                'id' => 2,
                'title' => 'High Priority Banner',
                'description' => 'Priority 1',
                'image_url' => 'https://example.com/high.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 1,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ],
            [
                'id' => 3,
                'title' => 'Medium Priority Banner',
                'description' => 'Priority 3',
                'image_url' => 'https://example.com/medium.jpg',
                'action_url' => null,
                'is_active' => true,
                'priority' => 3,
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
            ],
        ]);

        $response = $this->getJson('/api/app/advertising/banners');

        $response->assertStatus(200);

        $banners = $response->json('data.banners');
        $this->assertCount(3, $banners);
        $this->assertEquals('High Priority Banner', $banners[0]['title']);
        $this->assertEquals('Medium Priority Banner', $banners[1]['title']);
        $this->assertEquals('Low Priority Banner', $banners[2]['title']);
    }

    public function test_returns_empty_array_when_no_active_banners()
    {
        Config::set('advertising.banners', []);

        $response = $this->getJson('/api/app/advertising/banners');

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                ],
                'data' => [
                    'banners' => [],
                    'total' => 0,
                ],
            ]);
    }
}