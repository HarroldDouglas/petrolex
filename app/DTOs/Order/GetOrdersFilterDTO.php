<?php

declare(strict_types=1);

namespace App\DTOs\Order;

use App\Casts\SpatieEnumCast;
use App\DTOs\BaseDTO;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use Spatie\LaravelData\Attributes\WithCast;

final class GetOrdersFilterDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $order_number = null,
        #[WithCast(SpatieEnumCast::class, OrderStatus::class)]
        public readonly ?OrderStatus $status = null,
        #[WithCast(SpatieEnumCast::class, DeliveryType::class)]
        public readonly ?DeliveryType $delivery_type = null,
        #[WithCast(SpatieEnumCast::class, PaymentMethod::class)]
        public readonly ?PaymentMethod $payment_method = null,
    ) {}
}
