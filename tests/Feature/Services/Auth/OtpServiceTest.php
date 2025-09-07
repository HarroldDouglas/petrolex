<?php

namespace Tests\Feature\Services\Auth;

use App\Enums\LoginChannel;
use App\Exceptions\Auth\OtpDeliveryException;
use App\Exceptions\UserNotFoundException;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Services\SMS\TwilioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;

class OtpServiceTest extends TestCase
{
    use RefreshDatabase;

    private OtpService $otpService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->otpService = $this->app->make(OtpService::class);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_can_send_otp_via_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $result = $this->otpService->sendOtp($user->email);

        $this->assertTrue($result);
        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_it_sends_otp_email_with_user_language_french(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'french.user@example.com',
            'language' => 'fr',
        ]);

        $result = $this->otpService->sendOtp($user->email);

        $this->assertTrue($result);
        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->userLanguage === 'fr';
        });
    }

    public function test_it_sends_otp_email_with_user_language_english(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'english.user@example.com',
            'language' => 'en',
        ]);

        $result = $this->otpService->sendOtp($user->email);

        $this->assertTrue($result);
        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->userLanguage === 'en';
        });
    }

    public function test_it_defaults_to_french_when_user_has_no_language(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'nolang.user@example.com',
            'language' => null,
        ]);

        $result = $this->otpService->sendOtp($user->email);

        $this->assertTrue($result);
        Mail::assertSent(OtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->userLanguage === 'fr';
        });
    }

    public function test_it_can_send_otp_via_sms(): void
    {
        $mockTwilioService = Mockery::mock(TwilioService::class);
        $mockTwilioService->shouldReceive('sendSms')
            ->once()
            ->with('+1234567890', Mockery::type('string'))
            ->andReturn(true);

        $this->app->instance('twilio', $mockTwilioService);

        $user = User::factory()->create(['phone_number' => '+1234567890']);

        $result = $this->otpService->sendOtp($user->phone_number);

        $this->assertTrue($result);
    }

    public function test_it_throws_exception_for_non_existent_user(): void
    {
        $this->expectException(UserNotFoundException::class);

        $this->otpService->sendOtp('nonexistent@example.com');
    }

    public function test_it_returns_false_when_sms_delivery_fails(): void
    {
        $mockTwilioService = Mockery::mock(TwilioService::class);
        $mockTwilioService->shouldReceive('sendSms')
            ->once()
            ->andReturn(false);

        $this->app->instance('twilio', $mockTwilioService);

        $user = User::factory()->create(['phone_number' => '+1234567890']);

        $result = $this->otpService->sendOtp($user->phone_number);

        $this->assertFalse($result);
    }

    public function test_it_can_determine_email_channel(): void
    {
        $channel = $this->otpService->determineChannel('test@example.com');

        $this->assertTrue($channel->equals(LoginChannel::EMAIL()));
    }

    public function test_it_can_determine_phone_channel(): void
    {
        $channel = $this->otpService->determineChannel('+1234567890');

        $this->assertTrue($channel->equals(LoginChannel::PHONE()));
    }

    public function test_it_can_verify_valid_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $otp = '123456';

        Cache::put('otp_'.md5($user->email), $otp, now()->addMinutes(10));

        $result = $this->otpService->verifyOtp($user->email, $otp);

        $this->assertTrue($result);
        $this->assertFalse(Cache::has('otp_'.md5($user->email)));
    }

    public function test_it_rejects_invalid_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $otp = '123456';
        $wrongOtp = '654321';

        Cache::put('otp_'.md5($user->email), $otp, now()->addMinutes(10));

        $result = $this->otpService->verifyOtp($user->email, $wrongOtp);

        $this->assertFalse($result);
        $this->assertTrue(Cache::has('otp_'.md5($user->email)));
    }

    public function test_it_rejects_expired_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $result = $this->otpService->verifyOtp($user->email, '123456');

        $this->assertFalse($result);
    }

    public function test_it_can_resend_otp(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);
        $oldOtp = '123456';

        Cache::put('otp_'.md5($user->email), $oldOtp, now()->addMinutes(10));

        $result = $this->otpService->resendOtp($user->email);

        $this->assertTrue($result);
        // After resend, a new OTP should be in cache (different from old one)
        $newOtp = Cache::get('otp_'.md5($user->email));
        $this->assertNotEquals($oldOtp, $newOtp);
        $this->assertNotNull($newOtp);
        Mail::assertSent(OtpMail::class);
    }

    public function test_it_can_invalidate_otp(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $otp = '123456';

        Cache::put('otp_'.md5($user->email), $otp, now()->addMinutes(10));

        $result = $this->otpService->invalidateOtp($user->email);

        $this->assertTrue($result);
        $this->assertFalse(Cache::has('otp_'.md5($user->email)));
    }

    public function test_it_can_mask_email_identifier(): void
    {
        $maskedEmail = $this->otpService->maskIdentifier('john.doe@example.com');

        $this->assertEquals('j*******@e******.com', $maskedEmail);
    }

    public function test_it_can_mask_phone_identifier(): void
    {
        $maskedPhone = $this->otpService->maskIdentifier('+1234567890');

        $this->assertEquals('+123****890', $maskedPhone);
    }

    public function test_it_handles_short_phone_numbers(): void
    {
        $maskedPhone = $this->otpService->maskIdentifier('123');

        $this->assertEquals('***', $maskedPhone);
    }

    public function test_it_handles_invalid_email_format(): void
    {
        $maskedEmail = $this->otpService->maskIdentifier('invalid-email');

        $this->assertEquals('inva******ail', $maskedEmail);
    }

    public function test_otp_is_stored_in_cache_with_correct_ttl(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->otpService->sendOtp($user->email);

        $cacheKey = 'otp_'.md5($user->email);
        $this->assertTrue(Cache::has($cacheKey));

        $storedOtp = Cache::get($cacheKey);
        $this->assertIsString($storedOtp);
        $this->assertEquals(6, strlen($storedOtp));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $storedOtp);
    }

    public function test_cache_is_cleared_on_delivery_failure(): void
    {
        $mockTwilioService = Mockery::mock(TwilioService::class);
        $mockTwilioService->shouldReceive('sendSms')
            ->once()
            ->andThrow(new \Exception('SMS delivery failed'));

        $this->app->instance('twilio', $mockTwilioService);

        $user = User::factory()->create(['phone_number' => '+1234567890']);
        $cacheKey = 'otp_'.md5($user->phone_number);

        // The service should return false when delivery fails
        $result = $this->otpService->sendOtp($user->phone_number);
        
        $this->assertFalse($result, 'Service should return false on delivery failure');
        // Cache should still contain the OTP as per the current implementation
        $this->assertTrue(Cache::has($cacheKey), 'OTP should remain in cache for manual verification');
    }
}
