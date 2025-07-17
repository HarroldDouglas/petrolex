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
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'product_type' => $this->product_type,
            'product_type_id' => $this->product_type_id,
            'created_at' => $this->created_at,
        ];

        if ($this->product_type->value === ProductType::BOTTLE()->value) {
            $data = array_merge($data, BottleTypeResource::make($this->productTypeInstance)->toArray($request));
        } elseif ($this->product_type->value === ProductType::ACCESSORY()->value) {
            $data = array_merge($data, AccessoryTypeResource::make($this->productTypeInstance)->toArray($request));
        }

        return $data;
    }
}
