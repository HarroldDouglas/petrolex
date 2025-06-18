<?php

namespace App\Livewire\Supply;

use App\DTOs\Supply\CreateSupplierDeliveryDTO;
use App\Http\Requests\Supply\CreateSupplyDeliveryRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateSupplyDelivery extends AbstractSupplyDeliveryForm
{
    protected function customRequest(): FormRequest
    {
        return new CreateSupplyDeliveryRequest;
    }

    public function mount()
    {
        $this->supply_date = now()->format('Y-m-d\TH:i');
        $this->is_active = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {
            $supplierDeliveryDTO = new CreateSupplierDeliveryDTO(
                title: $validatedData['title'],
                supply_date: $validatedData['supply_date'],
                description: $validatedData['description'],
                distribution_center_id: (int) $validatedData['distribution_center_id'],
                is_active: $validatedData['is_active'] ?? true,
                user_id: auth()->id(),
            );

            $supplierDelivery = $this->supplyDeliveryService->create($supplierDeliveryDTO->toArray());

            if ($supplierDelivery) {
                return redirect()->route('supplies.edit', $supplierDelivery->id)
                    ->with('success', 'Approvisionnement créé avec succès!');
            }

            session()->flash('error', 'Une erreur est survenue lors de la création de l\'approvisionnement.');

            return null;
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
