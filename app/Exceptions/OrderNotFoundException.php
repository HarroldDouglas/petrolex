<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderNotFoundException extends ModelNotFoundException
{
    /**
     * Get the default error message.
     */
    protected static function getDefaultMessage(): string
    {
        return "La commande demandée n'a pas été trouvée.";
    }

    /**
     * Create a new exception for an order with the given ID.
     */
    public static function forId(int $id): self
    {
        return new self("La commande avec l'identifiant {$id} n'a pas été trouvée.");
    }
}
