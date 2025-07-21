<?php

namespace App\Http\Api\Resources\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 *
 * @property int $id
 * @property string $order_number
 * @property \App\Enums\DeliveryType $delivery_type
 * @property \App\Enums\PaymentMethod $payment_method
 * @property float $total_amount
 * @property \App\Enums\OrderStatus $status
 * @property OrderItemResource[] $items
 * @property array $delivery_address
 */
class OrderResource extends JsonResource
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
            'order_number' => $this->order_number,
            'delivery_type' => $this->delivery_type,
            'payment_method' => $this->payment_method,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'delivery_address' => [
                'id' => $this->delivery_address_id,
                'name' => $this->deliveryAddress->fullAddress(),
            ],
        ];
    }
}
