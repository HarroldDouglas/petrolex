<?php

namespace App\Http\Api\Resources\Order;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 *
 * @property int $id
 * @property int $delivery_address_id
 * @property string $order_number
 * @property \App\Enums\DeliveryType $delivery_type
 * @property \App\Enums\PaymentMethod $payment_method
 * @property float $subtotal
 * @property float $delivery_fee
 * @property float $total_amount
 * @property string $order_date
 * @property string $delivery_date
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
            'subtotal' => $this->subtotal,
            'delivery_fee' => $this->delivery_fee,
            'total_amount' => $this->total_amount,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date,
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment' => [
                'id' => $this->payment?->id,
                'status' => $this->payment?->payment_status,
                'date' => $this->payment?->payment_date,
                'reference' => $this->payment?->payment_reference,
                'method' => $this->payment?->payment_method,
            ],
            'delivery_address' => [
                'id' => $this->delivery_address_id,
                'name' => $this->deliveryAddress->fullAddress(),
                'contact_name' => $this->deliveryAddress->contact_full_name,
                'email' => $this->deliveryAddress->email,
                'city' => $this->deliveryAddress->city,
                'country' => $this->deliveryAddress->country,
                'neighborhood' => $this->deliveryAddress->neighborhood,
                'address_precision' => $this->deliveryAddress->address_precision,
                'latitude' => $this->deliveryAddress->latitude,
                'longitude' => $this->deliveryAddress->longitude,
            ],
        ];
    }
}
