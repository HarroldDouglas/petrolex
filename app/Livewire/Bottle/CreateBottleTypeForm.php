<?php

namespace App\Livewire\Bottle;

use App\DTOs\BottleType\CreateBottleTypeDTO;
use App\DTOs\ProductCategory\ProductCategoryCityPriceDTO;
use App\Http\Requests\Bottletype\StoreBottleTypeRequest;
use Illuminate\Foundation\Http\FormRequest;

class CreateBottleTypeForm extends AbstractBottleTypeForm
{
    public function mount()
    {
        parent::mount();
    }

    protected function customRequest(): FormRequest
    {
        return new StoreBottleTypeRequest;
    }

    public function save()
    {
        try {
            $validatedData = $this->validate();

            /** @var ProductCategoryCityPriceDTO[] */
            $bottleTypeCityPrices = array_map(
                /** @param array{city_id: int, content_price: string|float, content_with_bottle_price: string|float} $cityPrice */
                fn (array $cityPrice): ProductCategoryCityPriceDTO => new ProductCategoryCityPriceDTO(
                    product_category_id: null,
                    city_id: $cityPrice['city_id'],
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
                full_price: $validatedData['full_price'],
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
