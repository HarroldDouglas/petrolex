<?php

use App\Mail\Order\OrderStatusChangedMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/preview-email/order-notification/{order?}', function ($orderId = null) {
    $order = $orderId ? Order::find($orderId) : Order::first();

    if (! $order) {
        return response('No orders found in database. Create an order first.', 404);
    }

    $user = $order->customer?->user ?? User::first();

    if (! $user) {
        return response('No users found in database.', 404);
    }

    $mail = new OrderStatusChangedMail(
        order: $order,
        user: $user,
        oldStatus: 'pending',
        newStatus: 'delivered'
    );

    return $mail->render();
})->name('preview.email.order');
