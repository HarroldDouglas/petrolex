<?php

namespace App\Services\SMS;

use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class TwilioService implements SmsServiceInterface
{
    protected Client $client;
    protected string $from;

    /**
     * Create a new Twilio service instance.
     */
    public function __construct(string $sid, string $token, string $from)
    {
        $this->client = new Client($sid, $token);
        $this->from = $from;
    }

    /**
     * Send an SMS message.
     */
    public function sendSms(string $to, string $message): bool
    {
        try {
            $this->client->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'body' => $message,
                ]
            );

            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed: '.$e->getMessage());

            return false;
        }
    }
}
