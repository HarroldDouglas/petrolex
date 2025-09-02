<?php

namespace App\Mail;

use App\Enums\Language;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $otp,
        public string $maskedIdentifier,
        public ?string $userLanguage = null,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->getLocalizedSubject(),
        );
    }

    /**
     * Get the localized subject for the email
     */
    private function getLocalizedSubject(): string
    {
        $locale = $this->userLanguage ?? Language::default();

        return match ($locale) {
            'en' => __('email.otp_subject', [], 'en'),
            default => __('email.otp_subject', [], 'fr'),
        };
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-final',
            with: [
                'otp' => $this->otp,
                'maskedIdentifier' => $this->maskedIdentifier,
                'userLanguage' => $this->userLanguage ?? Language::default(),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
