<?php

namespace App\Repositories\Contracts;

use App\Models\BottleType;

interface BottleTypeRepositoryInterface extends baseRepositoryInterface
{
    /**
     * Create a new bottle type
     */
    public function create(array $data): BottleType;
}
