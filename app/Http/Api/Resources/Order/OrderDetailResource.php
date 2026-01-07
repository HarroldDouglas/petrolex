<?php

namespace App\Http\Api\Resources\Order;

use App\Enums\BottleOrderType;
use App\Http\Api\Resources\CustomerResource;
use App\Http\Api\Resources\DeliveryPersonResource;
use App\Http\Api\Resources\DistributionCenterResource;
use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Order&\Illuminate\Database\Eloquent\Model $order */
        $order = $this->resource;

        // Load delivery address with all geographic relations
        $order->loadMissing([
            'deliveryAddress.neighborhood.municipality.city.country',
        ]);

        return [
            // Basic order information
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'status_label' => $order->status->label ?? $order->status->value,
            'delivery_type' => $order->delivery_type->value,
            'delivery_type_label' => $order->delivery_type->label ?? $order->delivery_type->value,

            // Amounts and pricing
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'total_amount' => $order->total_amount,
            'wallet_amount_used' => $order->wallet_amount_used ?? '0.00',
            'total_amount_to_pay' => number_format((float) $order->total_amount - ((float) $order->wallet_amount_used ?? 0), 2, '.', ''),
            'total_refunded_amount' => $order->getTotalRefundedAmount(),

            // Important dates
            'order_date' => $order->order_date?->toISOString(),
            'delivery_date' => $order->delivery_date?->toISOString(),
            'paid_at' => $order->paid_at?->toISOString(),
            'processing_at' => $order->processing_at?->toISOString(),
            'delivered_at' => $order->delivered_at?->toISOString(),
            'cancelled_at' => $order->cancelled_at?->toISOString(),
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),

            // Comments and notes
            'comments' => $order->comments,
            'center_comments' => $order->center_comments,
            'rating' => $order->rating ? (float) $order->rating : null,
            'cancelled_by' => $order->cancelled_by,
            'cancelled_reason' => $order->cancelled_reason,

            // Customer information
            'customer' => new CustomerResource($order->customer),

            // Complete delivery address
            'delivery_address' => new CustomerDeliveryAddressResource($order->deliveryAddress),

            // Delivery person information
            'delivery_person' => new DeliveryPersonResource($order->deliveryPerson),

            // Distribution center
            'distribution_center' => new DistributionCenterResource($order->distributionCenter),

            // Payment information
            'payment' => $order->payment ? [
                'id' => $order->payment->id,
                'payment_method' => $order->payment->payment_method->value,
                'payment_method_label' => $order->payment->payment_method->label ?? $order->payment->payment_method->value,
                'payment_method_validation_text' => $order->payment->payment_method->validationText(),
                'payment_status' => $order->payment->payment_status->value,
                'payment_status_label' => $order->payment->payment_status->label ?? $order->payment->payment_status->value,
                'amount_paid' => $order->payment->amount_paid,
                'amount_due' => $order->payment->amount_due,
                'payment_reference' => $order->payment->payment_reference,
                'transaction_reference' => $order->payment->transaction_reference,
                'payment_url' => $order->payment->payment_url,
                'gateway_response' => $order->payment->gateway_response,
                'payment_date' => $order->payment->payment_date?->toISOString(),
                'payment_notes' => $order->payment->payment_notes,
                'created_at' => $order->payment->created_at->toISOString(),
            ] : null,

            // Order items with complete details
            'items' => $order->items->map(function ($item): array {
                $category = $item->productCategory;
                $image = null;
                if ($category && method_exists($category, 'getImages')) {
                    $images = $category->getImages();
                    $image = $images[0] ?? null;
                }

                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'total_price' => $item->total_price,
                    'bottle_type' => $item->bottle_type,
                    'bottle_type_label' => $item->bottle_type ? (BottleOrderType::from($item->bottle_type)->label ?? $item->bottle_type) : null,
                    'image' => $image,
                    'product_category' => $category ? [
                        'id' => $category->id,
                        'name' => $category->name,
                        'product_type' => $category->product_type->value,
                        'product_type_label' => $category->product_type->label ?? $category->product_type->value,
                    ] : null,
                ];
            })->toArray(),

            // Refunds
            'refunds' => $order->refunds->map(function ($refund): array {
                return [
                    'id' => $refund->id,
                    'amount' => $refund->amount,
                    'reason' => $refund->reason,
                    'status' => $refund->status->value,
                    'status_label' => $refund->status->label ?? $refund->status->value,
                    'refund_date' => $refund->refund_date?->toISOString(),
                    'created_at' => $refund->created_at->toISOString(),
                ];
            })->toArray(),

            // Delivery tracking
            'delivery_tracking' => $order->deliveryTracking ? [
                'id' => $order->deliveryTracking->id,
                'status' => $order->deliveryTracking->status->value,
                'status_label' => $order->deliveryTracking->status->label ?? $order->deliveryTracking->status->value,
                'current_latitude' => $order->deliveryTracking->driver_lat,
                'current_longitude' => $order->deliveryTracking->driver_lng,
                'estimated_duration' => $order->deliveryTracking->estimated_duration,
                'distance_remaining' => $order->deliveryTracking->distance_remaining,
                'started_at' => $order->deliveryTracking->started_at?->toISOString(),
                'delivered_at' => $order->deliveryTracking->delivered_at?->toISOString(),
                'created_at' => $order->deliveryTracking->created_at->toISOString(),
                'updated_at' => $order->deliveryTracking->updated_at->toISOString(),
            ] : null,

            // Destination coordinates
            'destination_coordinates' => [
                'latitude' => $order->destination_lat,
                'longitude' => $order->destination_lng,
            ],

            // States and permissions - Removed as not necessary for mobile

            // Bottle information
            'bottle_info' => [
                'has_bottle_items' => $order->hasBottleItems(),
                'has_refunds' => $order->hasRefunds(),
                'all_bottles_scanned' => $order->areAllBottlesScanned(),
                'bottle_scan_progress' => $order->bottle_scan_progress,
            ],

            // PDF invoice download link
            'invoice_url' => url("/api/orders/{$order->id}/download/invoice"),
        ];
    }
}
