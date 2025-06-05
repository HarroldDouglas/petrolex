<?php

namespace App\DTOs\BottleMovement;

use App\Enums\BottleMovementType;
use Carbon\Carbon;

class CreateBottleMovementDTO
{
    public function __construct(
        public readonly int $bottleId,
        public readonly BottleMovementType $type,
        public readonly int $userId,
        public readonly ?Carbon $movementDate = null,
        public readonly ?string $notes = null,
        public readonly ?int $supplierDeliveryId = null,
        public readonly ?int $distributionCenterId = null,
        public readonly ?int $deliveryPersonId = null,
        public readonly ?int $customerId = null,
        public readonly ?int $orderId = null,
        public readonly bool $declaredByCustomer = false,
    ) {}
    public function toArray(): array
    {
        return [
            'bottle_id' => $this->bottleId,
            'type' => $this->type,
            'user_id' => $this->userId,
            'movement_date' => $this->movementDate ?? now(),
            'notes' => $this->notes,
            'supplier_delivery_id' => $this->supplierDeliveryId,
            'distribution_center_id' => $this->distributionCenterId,
            'delivery_person_id' => $this->deliveryPersonId,
            'customer_id' => $this->customerId,
            'order_id' => $this->orderId,
            'declared_by_customer' => $this->declaredByCustomer,
        ];
    }
}