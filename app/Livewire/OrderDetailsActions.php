<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Component;

class OrderDetailsActions extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function cancelOrder($orderId, $reason)
    {
        $order = Order::find($orderId);

        if ($order && $order->canBeCancelled()) {
            $order->update([
                'status' => OrderStatus::CANCELLED(),
                'cancelled_at' => now(),
                'cancelled_reason' => $reason,
            ]);

            $this->dispatch('show-notification', [
                'type' => 'success',
                'title' => 'Commande annulée!',
                'text' => 'La commande a été annulée avec succès.',
                'timer' => 3000,
            ]);

            // Refresh the component to show the updated status
            return $this->redirect(request()->header('Referer'));
        } else {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Erreur',
                'text' => 'La commande ne peut pas être annulée.',
                'timer' => 3000,
            ]);
        }
    }

    public function render()
    {
        return view('components.order.detail.actions');
    }
}
