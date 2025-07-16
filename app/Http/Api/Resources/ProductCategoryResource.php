<?php

namespace App\Http\Api\Resources;

use App\Enums\ProductType;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductCategory */
class ProductCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'product_type' => $this->product_type,
            'product_type_id' => $this->product_type_id,
            'product_type_instance' => $this->when(
                $this->product_type->value === ProductType::BOTTLE()->value,
                BottleTypeResource::make($this->productTypeInstance)
            ),
            'accessory_type_instance' => $this->when(
                $this->product_type->value === ProductType::ACCESSORY()->value,
                AccessoryTypeResource::make($this->productTypeInstance)
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
