<?php

namespace App\DTOs\Order;

use App\Casts\SpatieEnumCast;
use App\DTOs\BaseDTO;
use App\Enums\DeliveryType;
use Spatie\LaravelData\Attributes\WithCast;

class CreateOrderWithoutPaymentDTO extends BaseDTO
{
    public function __construct(
        public int $customer_id,
        public int $delivery_address_id,
        public int $distribution_center_id,
        #[WithCast(SpatieEnumCast::class, DeliveryType::class)]
        public DeliveryType $delivery_type,
        /** @var OrderItemDTO[] */
        public array $items,
        public float $delivery_fee,
        public float $total_amount,
        public ?string $comments = null
    ) {}
}
