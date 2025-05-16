<?php

namespace App\Exceptions\Auth;

use Exception;

class OtpDeliveryException extends Exception
{
    public function __construct(string $message = 'Failed to deliver OTP')
    {
        parent::__construct($message);
    }
}
