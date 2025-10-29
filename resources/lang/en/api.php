<?php

return [
    // Authentication messages
    'login_success' => 'Login successful',
    'login_failed' => 'Invalid credentials',
    'logout_success' => 'Logout successful',
    'unauthorized' => 'Unauthorized',
    'token_expired' => 'Token expired',
    'token_invalid' => 'Invalid token',

    // Customer messages
    'customer_created_success' => 'Customer created successfully. An OTP has been sent for verification.',
    'customer_updated_success' => 'Customer profile updated successfully',
    'customer_not_found' => 'Customer not found',

    // Profile messages
    'profile_updated_success' => 'Profile updated successfully',
    'profile_not_found' => 'Profile not found',

    // OTP messages
    'otp_sent_success' => 'OTP code sent successfully',
    'otp_verified_success' => 'OTP code verified successfully',
    'otp_invalid' => 'Invalid OTP code',
    'otp_expired' => 'Expired OTP code',

    // Validation messages
    'validation_failed' => 'Validation failed',
    'field_required' => 'This field is required',
    'field_invalid' => 'This field is not valid',
    'email_invalid' => 'The email address is not valid',
    'phone_invalid' => 'The phone number is not valid',
    'password_min_length' => 'The password must be at least :min characters',

    // Address messages
    'address_created_success' => 'Delivery address created successfully',
    'address_updated_success' => 'Delivery address updated successfully',
    'address_deleted_success' => 'Delivery address deleted successfully',
    'address_not_found' => 'Address not found',

    // Order messages
    'order_created_success' => 'Order created successfully. Proceed to payment.',
    'order_details_retrieved' => 'Order details retrieved successfully',
    'order_cancelled_success' => 'Order cancelled successfully',
    'order_delivered_success' => 'Order marked as delivered successfully',
    'order_comment_added_success' => 'Comment added to order successfully',
    'order_not_belongs_to_you' => 'This order does not belong to you',
    'order_cannot_be_cancelled' => 'This order can no longer be cancelled. Only pending or paid orders can be cancelled.',
    'order_cannot_be_delivered' => 'This order cannot be marked as delivered',
    'order_cannot_accept_payment' => 'This order cannot accept payment. Orders with pending or completed payments cannot be paid again.',
    'order_cannot_receive_feedback' => 'You can only leave feedback on delivered or cancelled orders',
    'order_invoice_not_belongs_to_you' => 'This invoice does not belong to you',
    'order_not_authorized_to_deliver' => 'You are not authorized to mark this order as delivered',
    'order_not_authorized_to_scan_bottles' => 'You are not authorized to scan bottles for this order',
    'order_not_authorized_role_required' => 'You must be a customer or delivery person to access this resource',
    'customer_orders_retrieved_success' => 'Customer orders retrieved successfully',
    'delivery_person_orders_retrieved_success' => 'Delivery person orders retrieved successfully',

    // Payment messages
    'payment_initiated_success' => 'Payment initiated successfully',
    'payment_not_belongs_to_you' => 'This payment does not belong to you',

    // Bottle scanning messages
    'empty_bottle_scanned_success' => 'Empty bottle scanned successfully',
    'empty_bottle_scan_failed' => 'Failed to scan empty bottle',

    // Generic messages
    'operation_success' => 'Operation successful',
    'operation_failed' => 'Operation failed',
    'internal_server_error' => 'Internal server error',
    'resource_not_found' => 'Resource not found',
    'access_denied' => 'Access denied',
];
