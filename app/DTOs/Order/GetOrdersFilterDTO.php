<?php

declare(strict_types=1);

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;

final class GetOrdersFilterDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $order_number = null,
        public readonly ?OrderStatus $status = null,
        public readonly ?DeliveryType $delivery_type = null,
        public readonly ?PaymentMethod $payment_method = null,
    ) {}
}
