<?php

namespace App\Http\Api\Resources\Order;

use App\Enums\BottleOrderType;
use App\Http\Api\Resources\ProductCategoryResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderItem
 *
 * @property int $id
 * @property ProductCategoryResource $product_category
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property BottleOrderType|null $option
 * @property string $created_at
 */
class OrderItemResource extends JsonResource
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
            'product_category' => ProductCategoryResource::make($this->whenLoaded('productCategory')),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'option' => $this->bottle_type,
            'created_at' => $this->created_at,
        ];
    }
}
