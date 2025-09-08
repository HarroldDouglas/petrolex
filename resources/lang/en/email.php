<?php

return [
    // OTP Email
    'otp_subject' => 'Your verification code',
    'otp_greeting' => 'Hello!',
    'otp_message' => 'Your verification code for :app is available on',
    'use_code_message' => 'Use the following code to verify your account',
    'otp_expire' => 'This code expires in 10 minutes.',
    'otp_security' => 'If you did not request this code, please ignore this email.',
    'otp_footer' => 'Thank you for trusting <strong>:app</strong>.',

    // Welcome messages
    'welcome_subject' => 'Welcome to :app',
    'welcome_message' => 'We are delighted to welcome you!',

    // Common email elements
    'regards' => 'Regards',
    'team_signature' => 'The :app team',

    // Order notification emails - Simple unified approach
    'order_notification_subject' => 'Order #:order_number',
    'order_greeting' => 'Hello :user_name,',
    'order_status_message' => 'Your order **#:order_number** status is: **:status**',
    'default_user_name' => 'User',
    'unknown_customer' => 'Unknown Customer',
    
    'order_details' => 'Order Details',
    'order_number' => 'Order Number',
    'order_status' => 'Status',
    'order_total' => 'Total Amount',
    'order_customer' => 'Customer',
    'order_delivery_address' => 'Delivery Address',
    'order_delivered_at' => 'Delivered on',
    'view_order_button' => 'View Order',
    'order_footer_message' => 'Thank you for choosing our services.',

    // Order notification emails
    'order_created_customer_subject' => 'Order Confirmation #:order_number',
    'order_created_customer_greeting' => 'Hello :customer_name,',
    'order_created_customer_message' => 'Your order #:order_number has been successfully created and is being processed.',
    'order_created_customer_details' => 'Order Details:',
    'order_created_customer_total' => 'Total: :total',
    'order_created_customer_status' => 'Status: :status',
    'order_created_customer_address' => 'Delivery Address: :address',
    'order_created_customer_button' => 'View Order',
    'order_created_customer_footer' => 'Thank you for choosing our services.',

    'order_created_manager_subject' => 'New Order #:order_number',
    'order_created_manager_greeting' => 'Hello :manager_name,',
    'order_created_manager_message' => 'A new order #:order_number has been created in your distribution center.',
    'order_created_manager_details' => 'Order Details:',
    'order_created_manager_customer' => 'Customer: :customer_name',
    'order_created_manager_total' => 'Total: :total',
    'order_created_manager_status' => 'Status: :status',
    'order_created_manager_address' => 'Delivery Address: :address',
    'order_created_manager_button' => 'View Order',
    'order_created_manager_footer' => 'Please process this order promptly.',

    'order_delivered_customer_subject' => 'Order #:order_number Delivered',
    'order_delivered_customer_greeting' => 'Hello :customer_name,',
    'order_delivered_customer_message' => 'Great news! Your order #:order_number has been successfully delivered.',
    'order_delivered_customer_details' => 'Delivery Details:',
    'order_delivered_customer_delivered_at' => 'Delivered on: :delivered_at',
    'order_delivered_customer_total' => 'Total: :total',
    'order_delivered_customer_address' => 'Delivery Address: :address',
    'order_delivered_customer_button' => 'View Order',
    'order_delivered_customer_footer' => 'Thank you for choosing our services. We hope you are satisfied with your order.',

    'order_delivered_manager_subject' => 'Order #:order_number Delivered',
    'order_delivered_manager_greeting' => 'Hello :manager_name,',
    'order_delivered_manager_message' => 'Order #:order_number from your distribution center has been successfully delivered.',
    'order_delivered_manager_details' => 'Delivery Details:',
    'order_delivered_manager_customer' => 'Customer: :customer_name',
    'order_delivered_manager_delivered_at' => 'Delivered on: :delivered_at',
    'order_delivered_manager_total' => 'Total: :total',
    'order_delivered_manager_address' => 'Delivery Address: :address',
    'order_delivered_manager_button' => 'View Order',
    'order_delivered_manager_footer' => 'Order successfully completed.',

    'order_cancelled_customer_subject' => 'Order #:order_number Cancelled',
    'order_cancelled_customer_greeting' => 'Hello :customer_name,',
    'order_cancelled_customer_message' => 'We regret to inform you that your order #:order_number has been cancelled.',
    'order_cancelled_customer_details' => 'Cancellation Details:',
    'order_cancelled_customer_reason' => 'Reason: :reason',
    'order_cancelled_customer_total' => 'Refund Amount: :total',
    'order_cancelled_customer_button' => 'View Order',
    'order_cancelled_customer_footer' => 'If you have any questions, please contact our customer service.',

    'order_cancelled_manager_subject' => 'Order #:order_number Cancelled',
    'order_cancelled_manager_greeting' => 'Hello :manager_name,',
    'order_cancelled_manager_message' => 'Order #:order_number from your distribution center has been cancelled.',
    'order_cancelled_manager_details' => 'Cancellation Details:',
    'order_cancelled_manager_customer' => 'Customer: :customer_name',
    'order_cancelled_manager_reason' => 'Reason: :reason',
    'order_cancelled_manager_total' => 'Order Total: :total',
    'order_cancelled_manager_button' => 'View Order',
    'order_cancelled_manager_footer' => 'Please review the cancellation details.',
];
