<?php

namespace App\Http\Api\Controllers\TrackingDelivery\Concerns;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

trait AuthorizesDeliveryPerson
{
    /**
     * Ensure the authenticated user is the delivery person assigned to the order.
     * Aborts 403 otherwise (an unassigned driver must never touch the tracking).
     */
    protected function ensureAssignedDeliveryPerson(Order $order): void
    {
        $authenticatedUser = auth()->user();

        if (! $order->deliveryPerson || $order->deliveryPerson->user_id !== $authenticatedUser?->id) {
            Log::warning('Unauthorized delivery person access attempt', [
                'order_id' => $order->id,
                'authenticated_user_id' => $authenticatedUser?->id,
                'assigned_delivery_person_id' => $order->deliveryPerson?->user_id,
            ]);

            abort(403, __('api.order_not_authorized_to_deliver'));
        }
    }
}
