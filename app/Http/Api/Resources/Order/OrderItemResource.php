<?php

namespace App\Http\Api\Resources\Order;

use App\Http\Api\Resources\ProductCategoryResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrderItem */
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
            'order_id' => $this->order_id,
            'product_category_id' => $this->product_category_id,
            'product_category' => ProductCategoryResource::make($this->whenLoaded('productCategory')),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'option' => $this->bottle_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}