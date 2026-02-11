<?php

namespace App\DTOs\Dashboard;

class StatsDTO
{
    public function __construct(
        public readonly string $revenue,
        public readonly int $paidOrders,
        public readonly int $processingOrders,
        public readonly int $pendingOrders,
        public readonly int $deliveredOrders,
        public readonly int $canceledOrders,
    ) {}
}
