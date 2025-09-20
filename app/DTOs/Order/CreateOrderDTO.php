<?php

namespace App\DTOs\Order;

use App\Casts\SpatieEnumCast;
use App\DTOs\BaseDTO;
use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use Spatie\LaravelData\Attributes\WithCast;

class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $customer_id,
        public int $delivery_address_id,
        public int $distribution_center_id,
        #[WithCast(SpatieEnumCast::class, DeliveryType::class)]
        public DeliveryType $delivery_type,
        #[WithCast(SpatieEnumCast::class, PaymentMethod::class)]
        public PaymentMethod $payment_method,
        /** @var OrderItemDTO[] */
        public array $items,
        public ?string $comments = null
    ) {}
}
