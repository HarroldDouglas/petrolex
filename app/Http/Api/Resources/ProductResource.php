<?php

namespace App\Http\Api\Resources;

use App\Enums\ProductType;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ProductCategory $productCategory */
        $productCategory = $this->resource;
        $productTypeInstance = $productCategory->productTypeInstance;

        $data = [
            'id' => $productCategory->id,
            'type' => $productCategory->product_type->value,
            'nom' => $productTypeInstance->name,
            'description' => $productTypeInstance->description,
            'quantite' => $this->getQuantity(),
        ];

        if ($productCategory->product_type === ProductType::ACCESSORY()) {
            $data['prix'] = $productTypeInstance->price;
        }

        if ($productCategory->product_type === ProductType::BOTTLE()) {
            $data['capacite'] = $productTypeInstance->capacity;
            $data['hauteur'] = $productTypeInstance->height;
            $data['poids'] = $productTypeInstance->weight;
            $data['rayon'] = $productTypeInstance->radius;
            $data['prix_contenu'] = $productTypeInstance->content_price;
            $data['prix_bouteille_avec_contenu'] = $productTypeInstance->bottle_with_content_price;
        }

        return $data;
    }

    private function getQuantity(): int
    {
        /** @var ProductCategory $productCategory */
        $productCategory = $this->resource;

        if ($productCategory->product_type === ProductType::BOTTLE()) {
            return $productCategory->pivot->stock_filled ?? 0;
        }

        return $productCategory->pivot->stock ?? 0;
    }
}
