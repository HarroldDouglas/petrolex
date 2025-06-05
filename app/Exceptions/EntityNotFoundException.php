<?php

namespace App\Exceptions;

use Exception;

abstract class EntityNotFoundException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = '')
    {
        parent::__construct($message ?: $this->getDefaultMessage());
    }

    /**
     * Get the default error message.
     */
    abstract protected static function getDefaultMessage(): string;
}
