<?php

namespace Tests\Unit\Mail;

use App\Mail\OtpMail;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class OtpMailTest extends TestCase
{
    public function test_otp_mail_can_be_instantiated()
    {
        $mail = new OtpMail('123456', 't***@e***e.com');

        $this->assertInstanceOf(OtpMail::class, $mail);
        $this->assertEquals('123456', $mail->otp);
        $this->assertEquals('t***@e***e.com', $mail->maskedIdentifier);
        $this->assertNull($mail->userLanguage);
    }

    public function test_otp_mail_with_french_language()
    {
        $mail = new OtpMail('123456', 't***@e***e.com', 'fr');

        $this->assertEquals('fr', $mail->userLanguage);
    }

    public function test_otp_mail_with_english_language()
    {
        $mail = new OtpMail('123456', 't***@e***e.com', 'en');

        $this->assertEquals('en', $mail->userLanguage);
    }

    public function test_envelope_subject_is_localized_french()
    {
        App::setLocale('fr');
        $mail = new OtpMail('123456', 't***@e***e.com', 'fr');
        $envelope = $mail->envelope();

        $this->assertEquals('Votre code de vérification', $envelope->subject);
    }

    public function test_envelope_subject_is_localized_english()
    {
        App::setLocale('en');
        $mail = new OtpMail('123456', 't***@e***e.com', 'en');
        $envelope = $mail->envelope();

        $this->assertEquals('Your verification code', $envelope->subject);
    }

    public function test_content_uses_correct_view()
    {
        $mail = new OtpMail('123456', 't***@e***e.com', 'fr');
        $content = $mail->content();

        $this->assertEquals('emails.otp-final', $content->view);
    }

    public function test_content_passes_correct_variables()
    {
        $mail = new OtpMail('123456', 't***@e***e.com', 'fr');
        $content = $mail->content();

        $this->assertArrayHasKey('otp', $content->with);
        $this->assertArrayHasKey('maskedIdentifier', $content->with);
        $this->assertArrayHasKey('userLanguage', $content->with);

        $this->assertEquals('123456', $content->with['otp']);
        $this->assertEquals('t***@e***e.com', $content->with['maskedIdentifier']);
        $this->assertEquals('fr', $content->with['userLanguage']);
    }

    public function test_attachments_returns_empty_array()
    {
        $mail = new OtpMail('123456', 't***@e***e.com', 'fr');

        $this->assertEquals([], $mail->attachments());
    }
}
