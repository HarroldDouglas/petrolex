<?php

namespace App\Livewire\Order;

use App\Services\Order\OrderService;
use Livewire\Component;

class OrderDetailView extends Component
{
    public int $orderId;

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function render()
    {
        $orderDetails = app(OrderService::class)->getOrderWithGroupedItems($this->orderId);

        return view('livewire.order.order-detail-view', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);
    }
}
