<?php

namespace App\Livewire\Bottle;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\Http\Requests\Bottletype\StoreBottleTypeRequest;
use App\Models\DistributionCenter;
use Illuminate\Foundation\Http\FormRequest;

class CreateBottleTypeForm extends AbstractBottleTypeForm
{
    public function mount()
    {
        $this->availableCities = DistributionCenter::distinct()
            ->pluck('city')
            ->toArray();
    }

    protected function customRequest(): FormRequest
    {
        return new StoreBottleTypeRequest;
    }

    public function save()
    {
        $validatedData = $this->validate();
        $validatedData['cityPrices'] = $this->cityPrices;
        try {
            $bottleTypeDTO = new CreateBottleTypeDTO(
                name: $validatedData['name'],
                bottleTypeCityPrices: $validatedData['cityPrices'],
                capacity: $validatedData['capacity'],
                content_price: $validatedData['content_price'],
                bottle_with_content_price: $validatedData['bottle_with_content_price'],
                is_active: $validatedData['is_active'],
                description: $validatedData['description'],
                weight: $validatedData['weight'] ? (float) $validatedData['weight'] : null,
            );

            $this->bottleTypeService->create($bottleTypeDTO);

            session()->flash('success', 'Type de bouteille créé avec succès!');

            return redirect()->route('bottles.types.index');
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
