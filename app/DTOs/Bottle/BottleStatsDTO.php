<?php

namespace App\DTOs\Bottle;

use App\DTOs\BaseDTO;

class BottleStatsDTO extends BaseDTO
{
    public function __construct(
        public readonly int $inStock,
        public readonly int $withDeliveryPerson,
        public readonly int $withClient,
        public readonly int $lostStolen,
    ) {}
}
