<?php

namespace App\DTOs\Order;

use App\Casts\SpatieEnumCast;
use App\DTOs\BaseDTO;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Spatie\LaravelData\Attributes\WithCast;

class UpdateOrderDTO extends BaseDTO
{
    public function __construct(
        public ?OrderStatus $status,
    ) {}
}
