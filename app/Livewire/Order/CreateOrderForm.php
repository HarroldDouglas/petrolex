<?php

namespace App\Livewire\Order;

use App\Http\Requests\Order\StoreOrderRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class CreateOrderForm extends AbstractOrderForm
{
    protected function customRequest(): FormRequest
    {
        return new StoreOrderRequest;
    }

    public function mount()
    {
        Log::info('create component mounted', ['before initialize']);
        parent::initialize();
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {

            // Add creation call logic here

            session()->flash('success', 'Commande créée avec succès!');

            return redirect()->route('orders.list')
                ->with('success', 'Commande créée avec succès!');

        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
