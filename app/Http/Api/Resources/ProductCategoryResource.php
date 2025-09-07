<?php

namespace App\Http\Api\Resources;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductCategory
 *
 * @property int $id
 * @property string $name
 * @property \App\Enums\ProductType $product_type
 */
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
            'specifications' => $this->productTypeInstance->specifications ?? [],
        ];

        return $data;
    }
}
