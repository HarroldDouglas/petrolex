<?php

namespace App\DTOs\Order;

use App\Models\OrderItem;
use App\Models\Product;

class GroupedOrderItemDTO
{
    public function __construct(
        public OrderItem $orderItem,
        public int $groupedQuantity,
        public int $groupedTotalPrice,
        public string $displayName,
        public ?string $groupKey = null,
    ) {}

    /**
     * Get the unit price for display
     */
    public function getUnitPrice(): float
    {
        return (float) $this->orderItem->unit_price;
    }

    /**
     * Get the product type
     */
    public function getProductType(): string
    {
        return $this->orderItem->productType()->value;
    }

    /**
     * Check if this is a bottle item
     */
    public function isBottle(): bool
    {
        return $this->orderItem->isBottle();
    }

    /**
     * Check if this is an accessory item
     */
    public function isAccessory(): bool
    {
        return $this->orderItem->isAccessory();
    }
}
