<?php

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;
use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

class CreateOrderDTO extends BaseDTO
{
    public function __construct(
        public int $customer_id,
        public int $delivery_address_id,
        public int $distribution_center_id,
        public DeliveryType $delivery_type,
        public PaymentMethod $payment_method,
        #[DataCollectionOf(OrderItemDTO::class)]
        public DataCollection $items,
    ) {}
}
