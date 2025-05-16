<?php

namespace App\Exceptions;

use Exception;

class SmsDeliveryException extends Exception
{
    public function __construct(string $message = 'Failed to deliver SMS', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
