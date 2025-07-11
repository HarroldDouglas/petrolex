<?php

namespace App\Livewire\Order;

class CreateOrderForm extends AbstractOrderForm
{
    public function mount()
    {
        parent::initialize();
    }

    public function save()
    {

        try {

            // Add creation API call logic here

            session()->flash('success', 'Commande créée avec succès!');

            return redirect()->route('orders.list')
                ->with('success', 'Commande créée avec succès!');

        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
