<?php

namespace App\Services\SMS;

interface SmsServiceInterface
{
    /**
     * Create a new Twilio service instance.
     */
    public function __construct(string $sid, string $token, string $from);

    /**
     * Send an SMS message.
     */
    public function sendSms(string $to, string $message): bool;
}
