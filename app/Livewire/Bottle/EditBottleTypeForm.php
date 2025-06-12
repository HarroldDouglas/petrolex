<?php

namespace App\Livewire\Bottle;

use App\DTOs\BottleType\BottleTypeCityPriceDTO;
use App\DTOs\BottleType\UpdateBottleTypeDTO;
use App\Http\Requests\Bottletype\UpdateBottleTypeRequest; // Create this or use Store
use App\Models\BottleType;
use App\Models\BottleTypeCityPrice;
use App\Models\DistributionCenter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EditBottleTypeForm extends AbstractBottleTypeForm
{
    public BottleType $bottleType;
    public int $id;

    public function mount(BottleType $bottleType)
    {
        $this->bottleType = $bottleType;
        $this->availableCities = DistributionCenter::distinct()
            ->pluck('city')
            ->toArray();

        $this->loadBottleTypeData();
    }

    protected function loadBottleTypeData()
    {
        $this->id = $this->bottleType->id;
        $this->name = $this->bottleType->name;
        $this->capacity = $this->bottleType->capacity;
        $this->content_price = $this->bottleType->content_price;
        $this->bottle_with_content_price = $this->bottleType->bottle_with_content_price;
        $this->is_active = $this->bottleType->is_active;
        $this->description = $this->bottleType->description;
        $this->weight = $this->bottleType->weight;
        
        /** @var Collection<int, BottleTypeCityPrice> $cityPrices */
        $cityPrices = $this->bottleType->cityPrices()->get();

        $this->cityPrices = $cityPrices->map(function ($cityPrice) {
                return [
                    'id' => $cityPrice->id,
                    'city' => $cityPrice->city,
                    'content_price' => $cityPrice->content_price,
                    'content_with_bottle_price' => $cityPrice->content_with_bottle_price,
                ];
            })
            ->toArray();
    }

    protected function customRequest(): FormRequest
    {
        return new UpdateBottleTypeRequest;
    }

    public function save()
    {
        $validatedData = $this->validate();
        $validatedData['cityPrices'] = $this->cityPrices;

        try {
            $bottleTypeCityPrices = array_map(
                fn ($cityPrice) => new BottleTypeCityPriceDTO(
                    bottle_type_id: $this->bottleType->id,
                    city: $cityPrice['city'],
                    content_price: (float) $cityPrice['content_price'],
                    content_with_bottle_price: (float) $cityPrice['content_with_bottle_price'],
                ), $validatedData['cityPrices']
            );

            $bottleTypeDTO = new UpdateBottleTypeDTO(
                id: $this->bottleType->id,
                name: $validatedData['name'],
                bottleTypeCityPrices: $bottleTypeCityPrices,
                capacity: $validatedData['capacity'],
                content_price: $validatedData['content_price'],
                bottle_with_content_price: $validatedData['bottle_with_content_price'],
                is_active: $validatedData['is_active'],
                description: $validatedData['description'],
                weight: $validatedData['weight'] ? (float) $validatedData['weight'] : null,
            );

            $this->bottleTypeService->update($this->bottleType->id, $bottleTypeDTO);

            session()->flash('success', 'Type de bouteille modifié avec succès!');

            return redirect()->route('bottles.types.index');
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
