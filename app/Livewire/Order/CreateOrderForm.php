<?php

namespace App\Livewire\Order;

use App\DTOs\Order\CreateOrderDTO;
use App\Http\Requests\Order\StoreOrderRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrderForm extends AbstractOrderForm
{
    protected function customRequest(): FormRequest
    {
        return new StoreOrderRequest;
    }

    public function mount() {}

    public function save()
    {
        $validatedData = $this->validate();

        try {
            $dto = CreateOrderDTO::from($validatedData);
            $dtoArray = $dto->toArray();

            /** @var Order */
            $order = $this->orderService->create($this->order, $dtoArray);

            session()->flash('success', 'Commande créée avec succès!');

            return redirect()->route('orders.list')
                ->with('success', 'Commande créée avec succès!');

        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
