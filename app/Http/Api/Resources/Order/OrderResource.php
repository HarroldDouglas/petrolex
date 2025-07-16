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
            'customer_id' => $this->customer_id,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'delivery_address_id' => $this->delivery_address_id,
            'delivery_address' => CustomerDeliveryAddressResource::make($this->whenLoaded('deliveryAddress')),
            'distribution_center_id' => $this->distribution_center_id,
            'distribution_center' => DistributionCenterResource::make($this->whenLoaded('distributionCenter')),
            'delivery_type' => $this->delivery_type,
            'payment_method' => $this->payment_method,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
