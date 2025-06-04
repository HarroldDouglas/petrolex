<?php

namespace App\Services\Bottle;

use App\Repositories\Contracts\BottleRepositoryInterface;

class BottleService
{
    public function __construct(
        private BottleRepositoryInterface $bottleRepository,
    ) {
        $this->bottleRepository = $bottleRepository;
    }
}
