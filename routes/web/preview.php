<?php

use App\Mail\Order\OrderStatusChangedMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/preview-email/order-notification/{order?}', function ($orderId = null) {
    // Get an order (use provided ID or first available order)
    $order = $orderId ? Order::find($orderId) : Order::first();
    
    if (!$order) {
        return response('No orders found in database. Create an order first.', 404);
    }

    // Get a user (customer or any user)
    $user = $order->customer?->user ?? User::first();
    
    if (!$user) {
        return response('No users found in database.', 404);
    }

    // Create the mail instance
    $mail = new OrderStatusChangedMail(
        order: $order,
        user: $user,
        oldStatus: 'pending',
        newStatus: 'delivered'
    );

    // Return the rendered email view
    return $mail->render();
})->name('preview.email.order');
