<?php

namespace App\Livewire;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\DeliveryPersonService;
use App\Services\Order\OrderService;
use Illuminate\Support\Collection;
use Livewire\Component;

class OrderDetailsActions extends Component
{
    public Order $order;

    public Collection $allDeliveryPersons;
    public ?int $newDeliveryPersonId = null;
    public ?string $updateReason = null;

    protected DeliveryPersonService $deliveryPersonService;
    protected OrderService $orderService;

    public function boot(DeliveryPersonService $deliveryPersonService, OrderService $orderService)
    {
        $this->deliveryPersonService = $deliveryPersonService;
        $this->orderService = $orderService;
    }

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->allDeliveryPersons = $this->deliveryPersonService->getAll();
        $this->updateReason = $order->delivery_person_update_reason;
    }

    public function assignDeliveryPerson()
    {
        if (!$this->order->canChangeDeliveryPerson()) {
            $this->dispatch('show-notification', [
                'type' => 'error',
                'title' => 'Action non autorisée',
                'text' => 'Le livreur ne peut être changé que pour une commande confirmée.',
                'timer' => 4000,
            ]);

            return;
        }

        // TODO move this into a custom request class
        $this->validate([
            'newDeliveryPersonId' => 'required|exists:delivery_persons,id',
            'updateReason' => 'required|string|min:10',
        ]);

        $this->orderService->assignDeliveryPerson($this->order, $this->newDeliveryPersonId, $this->updateReason);

        $this->dispatch('show-notification', [
            'type' => 'success',
            'title' => 'Livreur changé!',
            'text' => 'Le livreur a été changé avec succès.',
            'timer' => 3000,
        ]);

        $this->dispatch('close-modal', ['modalId' => 'changeDeliveryPersonModal']);

        return $this->redirect(request()->header('Referer'));
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
