<?php

namespace App\Livewire;

use App\Models\DeliveryTracking;
use Livewire\Component;

class DeliveryDashboard extends Component
{
    public $deliveries = [];
    public $selectedDelivery = null;

    protected $listeners = [
        'echo:delivery-tracking,position.updated' => 'handlePositionUpdate',
    ];

    public function mount()
    {
        $this->loadDeliveries();
    }

    public function loadDeliveries()
    {
        // Chercher seulement les livraisons avec position GPS (started, in_progress)
        // ou filtrer celles qui ont driver_lat et driver_lng non null
        $this->deliveries = DeliveryTracking::whereIn('status', ['started', 'in_progress'])
            ->whereNotNull('driver_lat')
            ->whereNotNull('driver_lng')
            ->where('driver_lat', '!=', '')
            ->where('driver_lng', '!=', '')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->toArray();

        logger('Livraisons avec GPS chargées:', [
            'count' => count($this->deliveries),
            'deliveries' => collect($this->deliveries)->map(function ($d) {
                return [
                    'order_number' => $d['order_number'],
                    'status' => $d['status'],
                    'has_gps' => ! empty($d['driver_lat']) && ! empty($d['driver_lng']),
                    'driver_position' => $d['driver_lat'].','.$d['driver_lng'],
                ];
            }),
        ]);
    }

    public function selectDelivery($orderNumber)
    {
        logger('Sélection de livraison:', ['orderNumber' => $orderNumber]);

        $delivery = DeliveryTracking::where('order_number', $orderNumber)->first();

        if ($delivery) {
            $this->selectedDelivery = $delivery->toArray();
            logger('Livraison sélectionnée:', $this->selectedDelivery);

            // Envoyer seulement l'objet, pas un tableau
            $this->dispatch('delivery-selected', $this->selectedDelivery);
        }
    }

    public function refreshDeliveries()
    {
        $this->loadDeliveries();
        $this->dispatch('deliveries-refreshed');
    }

    public function handlePositionUpdate($event)
    {
        logger('Position update reçue:', $event);

        $this->loadDeliveries();

        // Mettre à jour la livraison sélectionnée si c'est celle qui a été mise à jour
        if ($this->selectedDelivery && isset($this->selectedDelivery['order_number']) && $this->selectedDelivery['order_number'] === $event['order_number']) {
            $delivery = DeliveryTracking::where('order_number', $event['order_number'])->first();
            if ($delivery) {
                $this->selectedDelivery = $delivery->toArray();
            }
        }

        // Envoyer l'événement au JavaScript pour mettre à jour la carte
        $this->dispatch('position-updated', $event);
    }

    public function render()
    {
        return view('livewire.delivery-dashboard');
    }
}
