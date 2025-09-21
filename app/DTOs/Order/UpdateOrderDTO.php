<?php

namespace App\DTOs\Order;

use App\DTOs\BaseDTO;
use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use Illuminate\Support\Carbon;

class UpdateOrderDTO extends BaseDTO
{
    public function __construct(
        public ?int $id = null,
        public ?int $customer_id = null,
        public ?int $delivery_address_id = null,
        public ?int $delivery_person_id = null,
        public ?int $distribution_center_id = null,
        public ?string $order_number = null,
        public ?DeliveryType $delivery_type = null,
        public ?OrderStatus $status = null,
        public ?float $subtotal = null,
        public ?float $delivery_fee = null,
        public ?float $total_amount = null,
        public ?Carbon $order_date = null,
        public ?Carbon $delivery_date = null,
        public ?string $comments = null,
        public ?string $center_comments = null,
        public ?int $rating = null,
        public ?Carbon $paid_at = null,
        public ?Carbon $processing_at = null,
        public ?Carbon $cancelled_at = null,
        public ?int $cancelled_by = null,
        public ?string $cancelled_reason = null,
        public ?Carbon $delivered_at = null,
    ) {}
}
