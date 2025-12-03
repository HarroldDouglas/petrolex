<?php

namespace Tests\Feature\Api\App;

use App\Enums\AppType;
use App\Enums\Platform;
use App\Models\AppVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAppVersionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_version_for_customer_app_with_both_platforms()
    {
        // Create versions for both platforms
        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 10,
            'version_name' => '1.0.0',
            'update_required' => false,
            'release_notes' => 'Bug fixes and improvements for Android',
            'is_active' => true,
        ]);

        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::IOS()->value,
            'version_code' => 11,
            'version_name' => '1.0.1',
            'update_required' => true,
            'release_notes' => 'Bug fixes and improvements for iOS',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/app/version?app_type=customer_app');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '_metadata' => [
                    'success',
                    'message',
                ],
                'data' => [
                    'android' => [
                        'version_code',
                        'version_name',
                        'update_required',
                        'release_notes',
                    ],
                    'ios' => [
                        'version_code',
                        'version_name',
                        'update_required',
                        'release_notes',
                    ],
                ],
            ])
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                    'message' => 'Informations de version récupérées avec succès.',
                ],
                'data' => [
                    'android' => [
                        'version_code' => 10,
                        'version_name' => '1.0.0',
                        'update_required' => false,
                        'release_notes' => 'Bug fixes and improvements for Android',
                    ],
                    'ios' => [
                        'version_code' => 11,
                        'version_name' => '1.0.1',
                        'update_required' => true,
                        'release_notes' => 'Bug fixes and improvements for iOS',
                    ],
                ],
            ]);
    }

    public function test_can_get_version_for_delivery_app()
    {
        // Create versions for delivery app
        AppVersion::create([
            'app_type' => AppType::DELIVERY_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 5,
            'version_name' => '0.5.0',
            'update_required' => true,
            'release_notes' => 'New features for delivery persons',
            'is_active' => true,
        ]);

        AppVersion::create([
            'app_type' => AppType::DELIVERY_APP()->value,
            'platform' => Platform::IOS()->value,
            'version_code' => 5,
            'version_name' => '0.5.0',
            'update_required' => true,
            'release_notes' => 'New features for delivery persons',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/app/version?app_type=delivery_app');

        $response->assertStatus(200)
            ->assertJson([
                '_metadata' => [
                    'success' => true,
                ],
                'data' => [
                    'android' => [
                        'version_code' => 5,
                        'version_name' => '0.5.0',
                        'update_required' => true,
                    ],
                    'ios' => [
                        'version_code' => 5,
                        'version_name' => '0.5.0',
                        'update_required' => true,
                    ],
                ],
            ]);
    }

    public function test_returns_only_active_versions()
    {
        // Create an inactive version
        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 5,
            'version_name' => '0.5.0',
            'update_required' => false,
            'is_active' => false,
        ]);

        // Create an active version
        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 10,
            'version_name' => '1.0.0',
            'update_required' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/app/version?app_type=customer_app');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'android' => [
                        'version_code' => 10,
                        'version_name' => '1.0.0',
                    ],
                ],
            ]);
    }

    public function test_returns_latest_version_when_multiple_active_versions_exist()
    {
        // Create multiple active versions
        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 8,
            'version_name' => '0.8.0',
            'update_required' => false,
            'is_active' => true,
        ]);

        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 10,
            'version_name' => '1.0.0',
            'update_required' => false,
            'is_active' => true,
        ]);

        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 9,
            'version_name' => '0.9.0',
            'update_required' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/app/version?app_type=customer_app');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'android' => [
                        'version_code' => 10,
                        'version_name' => '1.0.0',
                    ],
                ],
            ]);
    }

    public function test_returns_null_for_platform_without_version()
    {
        // Create version only for Android
        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 10,
            'version_name' => '1.0.0',
            'update_required' => false,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/app/version?app_type=customer_app');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'android' => [
                        'version_code' => 10,
                        'version_name' => '1.0.0',
                    ],
                    'ios' => null,
                ],
            ]);
    }

    public function test_returns_404_when_no_versions_available()
    {
        $response = $this->getJson('/api/app/version?app_type=customer_app');

        $response->assertStatus(404)
            ->assertJson([
                '_metadata' => [
                    'success' => false,
                    'message' => 'Aucune version disponible pour cette application.',
                ],
            ]);
    }

    public function test_returns_validation_error_when_app_type_is_missing()
    {
        $response = $this->getJson('/api/app/version');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['app_type']);
    }

    public function test_returns_validation_error_when_app_type_is_invalid()
    {
        $response = $this->getJson('/api/app/version?app_type=invalid_app');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['app_type']);
    }

    public function test_separates_versions_between_different_app_types()
    {
        // Create versions for customer app
        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 10,
            'version_name' => '1.0.0',
            'update_required' => false,
            'is_active' => true,
        ]);

        // Create versions for delivery app
        AppVersion::create([
            'app_type' => AppType::DELIVERY_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 5,
            'version_name' => '0.5.0',
            'update_required' => true,
            'is_active' => true,
        ]);

        // Request customer app version
        $customerResponse = $this->getJson('/api/app/version?app_type=customer_app');
        $customerResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'android' => [
                        'version_code' => 10,
                        'version_name' => '1.0.0',
                    ],
                ],
            ]);

        // Request delivery app version
        $deliveryResponse = $this->getJson('/api/app/version?app_type=delivery_app');
        $deliveryResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'android' => [
                        'version_code' => 5,
                        'version_name' => '0.5.0',
                    ],
                ],
            ]);
    }

    public function test_release_notes_can_be_null()
    {
        AppVersion::create([
            'app_type' => AppType::CUSTOMER_APP()->value,
            'platform' => Platform::ANDROID()->value,
            'version_code' => 10,
            'version_name' => '1.0.0',
            'update_required' => false,
            'release_notes' => null,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/app/version?app_type=customer_app');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'android' => [
                        'version_code' => 10,
                        'version_name' => '1.0.0',
                        'update_required' => false,
                        'release_notes' => null,
                    ],
                ],
            ]);
    }
}
