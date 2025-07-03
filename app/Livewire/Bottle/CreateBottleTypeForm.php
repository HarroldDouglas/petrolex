<?php

namespace App\Livewire\Bottle;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\BottleType\ProductCategoryCityPriceDTO;
use App\Http\Requests\Bottletype\StoreBottleTypeRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class CreateBottleTypeForm extends AbstractBottleTypeForm
{
    public function mount()
    {
        $this->availableCities = $this->geographyService
            ->getCities(Config::get('geography.authorized-countries.CM.name'));
    }

    protected function customRequest(): FormRequest
    {
        return new StoreBottleTypeRequest;
    }

    public function save()
    {
        $validatedData = $this->validate();
        try {
            /** @var array<int, ProductCategoryCityPriceDTO> */
            $bottleTypeCityPrices = array_map(
                /** @param array{city: string, content_price: string|float, content_with_bottle_price: string|float} $cityPrice */
                fn (array $cityPrice): ProductCategoryCityPriceDTO => new ProductCategoryCityPriceDTO(
                    product_category_id: null,
                    city: $cityPrice['city'],
                    content_price: (float) $cityPrice['content_price'],
                    content_with_bottle_price: (float) $cityPrice['content_with_bottle_price'],
                ),
                $validatedData['cityPrices']
            );

            $bottleTypeDTO = new CreateBottleTypeDTO(
                name: $validatedData['name'],
                bottleTypeCityPrices: $bottleTypeCityPrices,
                capacity: $validatedData['capacity'],
                content_price: $validatedData['content_price'],
                bottle_with_content_price: $validatedData['bottle_with_content_price'],
                is_active: $validatedData['is_active'],
                description: $validatedData['description'],
                weight: $validatedData['weight'] ? (float) $validatedData['weight'] : null,
                images: $this->product_images ?: null,
            );

            $this->bottleTypeService->create($bottleTypeDTO->toArray());

            session()->flash('success', 'Type de bouteille créé avec succès!');

            return redirect()->route('bottles.types.index');
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
