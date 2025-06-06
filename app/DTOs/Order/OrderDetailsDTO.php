<?php

namespace App\DTOs\Order;

use App\Models\Order;
use Illuminate\Support\Collection;

class OrderDetailsDTO
{
    public function __construct(
        public Order $order,
        /** @var Collection<GroupedOrderItemDTO> */
        public Collection $groupedItems,
    ) {}

    /**
     * Get total quantity across all grouped items
     */
    public function getTotalQuantity(): int
    {
        return $this->groupedItems->sum('groupedQuantity');
    }

    /**
     * Get total amount across all grouped items
     */
    public function getTotalAmount(): int
    {
        return $this->groupedItems->sum('groupedTotalPrice');
    }

    /**
     * Get count of different item types
     */
    public function getItemTypesCount(): int
    {
        return $this->groupedItems->count();
    }
}
