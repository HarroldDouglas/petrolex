<?php

namespace Tests\Feature\Api\App;

use App\Models\MobileAppLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreMobileAppLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_mobile_app_log_without_authentication(): void
    {
        $payload = [
            'app_type' => 'customer_app',
            'platform' => 'android',
            'app_version' => '1.0.11',
            'device_model' => 'Samsung SM-A125F',
            'os_version' => 'Android 13',
            'message' => "type 'Null' is not a subtype of type 'String' in type cast",
            'stack_trace' => "#0 NeighborhoodModel.fromJson\n#1 MunicipalityModel.fromJson",
            'context' => ['screen' => 'login', 'endpoint' => '/api/distribution-centers/closest'],
            'occurred_at' => '2026-08-10T09:15:00Z',
        ];

        $response = $this->postJson('/api/app/logs', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('_metadata.success', true);

        $this->assertDatabaseCount('mobile_app_logs', 1);

        $log = MobileAppLog::first();
        $this->assertSame('customer_app', $log->app_type);
        $this->assertSame('error', $log->level);
        $this->assertSame('login', $log->context['screen']);
        $this->assertNull($log->user_id);
    }

    public function test_it_rejects_a_log_without_required_fields(): void
    {
        $response = $this->postJson('/api/app/logs', ['platform' => 'android']);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['app_type', 'message']);

        $this->assertDatabaseCount('mobile_app_logs', 0);
    }

    public function test_it_rejects_an_unknown_app_type(): void
    {
        $response = $this->postJson('/api/app/logs', [
            'app_type' => 'hacker_app',
            'message' => 'boom',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['app_type']);
    }
}
