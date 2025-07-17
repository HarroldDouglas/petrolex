<?php

namespace App\Http\Api\Resources\Order;

use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Resources\DistributionCenterResource;
use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
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
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'delivery_address' => CustomerDeliveryAddressResource::make($this->whenLoaded('deliveryAddress')),
            'distribution_center' => DistributionCenterResource::make($this->whenLoaded('distributionCenter')),
        ];
    }
}
