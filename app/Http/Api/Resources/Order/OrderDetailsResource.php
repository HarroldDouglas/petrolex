<?php

namespace App\Http\Api\Resources\Order;

use App\Enums\BottleOrderType;
use App\Http\Api\Resources\CustomerResource;
use App\Http\Resources\Customer\CustomerDeliveryAddressResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Order
 */
class OrderDetailsResource extends JsonResource
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

        return [
            // Informations de base de la commande
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status->value,
            'status_label' => $order->status->label ?? $order->status->value,
            'delivery_type' => $order->delivery_type->value,
            'delivery_type_label' => $order->delivery_type->label ?? $order->delivery_type->value,

            // Montants et prix
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'total_amount' => $order->total_amount,
            'total_refunded_amount' => $order->getTotalRefundedAmount(),

            // Dates importantes
            'order_date' => $order->order_date?->toISOString(),
            'delivery_date' => $order->delivery_date?->toISOString(),
            'confirmed_at' => $order->confirmed_at?->toISOString(),
            'processing_at' => $order->processing_at?->toISOString(),
            'delivered_at' => $order->delivered_at?->toISOString(),
            'cancelled_at' => $order->cancelled_at?->toISOString(),
            'created_at' => $order->created_at->toISOString(),
            'updated_at' => $order->updated_at->toISOString(),

            // Commentaires et notes
            'comments' => $order->comments,
            'center_comments' => $order->center_comments,
            'rating' => $order->rating,
            'cancelled_by' => $order->cancelled_by,
            'cancelled_reason' => $order->cancelled_reason,

            // Informations du client
            'customer' => $this->whenLoaded('customer', function () use ($order) {
                return new CustomerResource($order->customer);
            }),

            // Adresse de livraison complète
            'delivery_address' => $this->whenLoaded('deliveryAddress', function () use ($order) {
                return new CustomerDeliveryAddressResource($order->deliveryAddress);
            }),

            // Informations du livreur
            'delivery_person' => $this->whenLoaded('deliveryPerson', function () use ($order) {
                if (! $order->deliveryPerson) {
                    return null;
                }

                $deliveryPerson = $order->deliveryPerson;
                $user = $deliveryPerson->user;

                return [
                    'id' => $deliveryPerson->id,
                    'user_id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'full_name' => $user->full_name,
                    'phone_number' => $user->phone_number,
                    'email' => $user->email,
                ];
            }),

            // Centre de distribution
            'distribution_center' => $this->whenLoaded('distributionCenter', function () use ($order) {
                if (! $order->distributionCenter) {
                    return null;
                }

                $center = $order->distributionCenter;

                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'address' => $center->address,
                    'phone' => $center->phone,
                    'latitude' => $center->latitude,
                    'longitude' => $center->longitude,
                ];
            }),

            // Informations de paiement
            'payment' => $this->whenLoaded('payment', function () use ($order) {
                if (! $order->payment) {
                    return null;
                }

                $payment = $order->payment;

                return [
                    'id' => $payment->id,
                    'payment_method' => $payment->payment_method->value,
                    'payment_method_label' => $payment->payment_method->label ?? $payment->payment_method->value,
                    'payment_status' => $payment->payment_status->value,
                    'payment_status_label' => $payment->payment_status->label ?? $payment->payment_status->value,
                    'amount_paid' => $payment->amount_paid,
                    'amount_due' => $payment->amount_due,
                    'payment_reference' => $payment->payment_reference,
                    'transaction_reference' => $payment->transaction_reference,
                    'payment_url' => $payment->payment_url,
                    'gateway_response' => $payment->gateway_response,
                    'payment_date' => $payment->payment_date?->toISOString(),
                    'payment_notes' => $payment->payment_notes,
                    'created_at' => $payment->created_at->toISOString(),
                ];
            }),

            // Articles de la commande avec tous les détails
            'items' => $this->whenLoaded('items', function () use ($order): array {
                return $order->items->map(function ($item): array {
                    $category = $item->productCategory;

                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'bottle_type' => $item->bottle_type,
                        'bottle_type_label' => $item->bottle_type ? (BottleOrderType::from($item->bottle_type)->label ?? $item->bottle_type) : null,
                        'product_category' => $category ? [
                            'id' => $category->id,
                            'name' => $category->name,
                            'product_type' => $category->product_type->value,
                            'product_type_label' => $category->product_type->label ?? $category->product_type->value,
                        ] : null,
                    ];
                })->toArray();
            }),

            // Mouvements de bouteilles (consignes) - Supprimé car non pertinent pour mobile

            // Remboursements
            'refunds' => $this->whenLoaded('refunds', function () use ($order): array {
                return $order->refunds->map(function ($refund): array {
                    return [
                        'id' => $refund->id,
                        'amount' => $refund->amount,
                        'reason' => $refund->reason,
                        'status' => $refund->status->value,
                        'status_label' => $refund->status->label ?? $refund->status->value,
                        'refund_date' => $refund->refund_date?->toISOString(),
                        'created_at' => $refund->created_at->toISOString(),
                    ];
                })->toArray();
            }),

            // Suivi de livraison
            'delivery_tracking' => $this->whenLoaded('deliveryTracking', function () use ($order) {
                if (! $order->deliveryTracking) {
                    return null;
                }

                $tracking = $order->deliveryTracking;

                return [
                    'id' => $tracking->id,
                    'status' => $tracking->status->value,
                    'status_label' => $tracking->status->label ?? $tracking->status->value,
                    'current_latitude' => $tracking->driver_lat,
                    'current_longitude' => $tracking->driver_lng,
                    'estimated_duration' => $tracking->estimated_duration,
                    'distance_remaining' => $tracking->distance_remaining,
                    'started_at' => $tracking->started_at?->toISOString(),
                    'delivered_at' => $tracking->delivered_at?->toISOString(),
                    'created_at' => $tracking->created_at->toISOString(),
                    'updated_at' => $tracking->updated_at->toISOString(),
                ];
            }),

            // Coordonnées de destination
            'destination_coordinates' => [
                'latitude' => $order->destination_lat,
                'longitude' => $order->destination_lng,
            ],

            // États et permissions - Supprimé car non nécessaire pour mobile

            // Informations sur les bouteilles
            'bottle_info' => [
                'has_bottle_items' => $order->hasBottleItems(),
                'has_refunds' => $order->hasRefunds(),
                'all_bottles_scanned' => $order->areAllBottlesScanned(),
                'bottle_scan_progress' => $order->bottle_scan_progress,
            ],

            // Lien de téléchargement de la facture PDF
            'invoice_url' => url("/api/orders/{$order->id}/download/invoice"),
        ];
    }
}
