<?php

namespace App\Http\Api\Resources\Order;

use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Resources\DistributionCenterResource;
use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
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
 * @property OrderPaymentResource $payment
 * @property string|null $comments
 * @property float|null $rating
 * @property OrderItemResource[] $items
 * @property CustomerDeliveryAddressResource $delivery_address
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
            'ticket_url' => route('orders.download.invoice', ['order' => $this->id]),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'comments' => $this->comments,
            'rating' => $this->rating,
            'payment' => OrderPaymentResource::make($this->whenLoaded('payment')),
            'delivery_address' => CustomerDeliveryAddressResource::make($this->deliveryAddress),
            'customer' => CustomerResource::make($this->customer),
            'distribution_center' => DistributionCenterResource::make($this->whenLoaded('distributionCenter')),
        ];
    }
}
