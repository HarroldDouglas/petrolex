<?php

namespace App\Livewire\Bottle;

use App\DTOs\BottleType\ProductCategoryCityPriceDTO;
use App\DTOs\BottleType\UpdateBottleTypeDTO;
use App\Http\Requests\Bottletype\UpdateBottleTypeRequest;
use App\Models\BottleType;
use App\Models\ProductCategory;
use App\Models\ProductCategoryCityPrice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class EditBottleTypeForm extends AbstractBottleTypeForm
{
    public BottleType $bottleType;
    public int $id;

    public function mount(BottleType $bottleType)
    {
        $this->bottleType = $bottleType;
        $this->availableCities = $this->geographyService
            ->getCities(Config::get('geography.authorized-countries.CM.name'));

        $this->loadBottleTypeData();
    }

    /**
     * Transform ProductCategoryCityPrice models to DTOs for editing
     */
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

        $this->bottleType->load('media');
        $this->existingImages = $this->bottleType->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'file_name' => $media->file_name,
                'mime_type' => $media->mime_type,
                'original_url' => $media->getUrl(),
                'preview_url' => $media->getUrl('thumb'),
            ];
        })->toArray();

        $productCategory = ProductCategory::bottles()
            ->where('product_type_id', $this->bottleType->id)
            ->first();

        if ($productCategory) {
            /** @var Collection<int, ProductCategoryCityPrice> $cityPrices */
            $cityPrices = ProductCategoryCityPrice::where('product_category_id', $productCategory->id)->get(); // TODO: move this into the repository

            /** @var array<int, ProductCategoryCityPriceDTO> $cityPriceDTOs */
            $cityPriceDTOs = $cityPrices->map(
                function (ProductCategoryCityPrice $cityPrice) use ($productCategory): ProductCategoryCityPriceDTO {
                    return new ProductCategoryCityPriceDTO(
                        product_category_id: $productCategory->id,
                        city: $cityPrice->city,
                        content_price: (float) $cityPrice->content_price,
                        content_with_bottle_price: (float) $cityPrice->content_with_bottle_price
                    );
                }
            )->toArray();

            $this->cityPrices = $cityPriceDTOs;
        } else {
            $this->cityPrices = [];
        }
    }

    protected function customRequest(): FormRequest
    {
        return new UpdateBottleTypeRequest($this->bottleType->id);
    }

    public function save()
    {
        $validatedData = $this->validate();

        try {
            /** @var ProductCategory $productCategory */
            $productCategory = ProductCategory::bottles()
                ->where('product_type_id', $this->bottleType->id)
                ->first();

            /** @var array<int, ProductCategoryCityPriceDTO> */
            $bottleTypeCityPrices = array_map(
                /** @param array{city: string, content_price: string|float, content_with_bottle_price: string|float} $cityPrice */
                fn (array $cityPrice): ProductCategoryCityPriceDTO => new ProductCategoryCityPriceDTO(
                    product_category_id: $productCategory->id,
                    city: $cityPrice['city'],
                    content_price: (float) $cityPrice['content_price'],
                    content_with_bottle_price: (float) $cityPrice['content_with_bottle_price'],
                ),
                $validatedData['cityPrices']
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
                images: $this->product_images ?: null,
            );

            $this->bottleTypeService->update(
                $this->bottleType, 
                $bottleTypeDTO->toArray(), 
                ! empty($this->imagesIdsToDelete) ? $this->imagesIdsToDelete : null
            );

            session()->flash('success', 'Type de bouteille modifié avec succès!');

            return redirect()->route('bottles.types.edit', $this->bottleType->id);
        } catch (\Throwable $th) {
            session()->flash('error', $th->getMessage());
            throw $th;
        }
    }
}
