<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserNotFoundException extends ModelNotFoundException
{
    public function __construct(string $identifier)
    {
        parent::__construct("User with identifier '$identifier' not found.");
    }
}
