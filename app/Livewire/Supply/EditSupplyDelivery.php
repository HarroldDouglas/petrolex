<?php

namespace App\Livewire\Supply;

use App\DTOs\Supply\UpdateSupplierDeliveryDTO;
use App\Http\Requests\Supply\UpdateSupplyDeliveryRequest;
use App\Models\SupplierDelivery;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class EditSupplyDelivery extends AbstractSupplyDeliveryForm
{
    public $supply;

    protected function customRequest(): FormRequest
    {
        return new UpdateSupplyDeliveryRequest;
    }

    public function mount(int $supplyId)
    {
        // TODO : replace this with a repository or service call
        $this->supply = SupplierDelivery::findOrFail($supplyId);

        $this->title = $this->supply->title;
        $this->supply_date = $this->supply->supply_date ?
            Carbon::parse($this->supply->supply_date)->format('Y-m-d\TH:i') :
            now()->format('Y-m-d\TH:i');
        $this->description = $this->supply->description;
        $this->distribution_center_id = $this->supply->distribution_center_id;
        $this->is_active = $this->supply->is_active ?? true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {
            $supplierDeliveryDTO = new UpdateSupplierDeliveryDTO(
                title: $validatedData['title'],
                supply_date: $validatedData['supply_date'],
                description: $validatedData['description'],
                distribution_center_id: (int) $validatedData['distribution_center_id'],
            );

            $updated = $this->supplyDeliveryService->update($this->supply, $supplierDeliveryDTO->toArrayFiltered());

            if ($updated) {
                session()->flash('success', 'Approvisionnement mis à jour avec succès!');
            } else {
                session()->flash('error', 'Une erreur est survenue lors de la mise à jour de l\'approvisionnement.');
            }

            return redirect()->route('supplies.edit', $this->supply->id);
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());

            return redirect()->route('supplies.edit', $this->supply->id);
        }
    }
}
