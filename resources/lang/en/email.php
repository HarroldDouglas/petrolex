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

    // Specific Order Status Email Translations

    // Order Created
    'order_created_subject' => 'New Order #:order_number Created',
    'order_created_message' => 'Your order **#:order_number** has been successfully created!',
    'order_created_footer_message' => 'We will process your order as soon as possible.',
    'order_created_note' => 'You will receive a notification as soon as your order is confirmed.',

    // Order Confirmed
    'order_confirmed_subject' => 'Order #:order_number Confirmed',
    'order_confirmed_message' => 'Great news! Your order **#:order_number** has been confirmed.',
    'order_confirmed_footer_message' => 'Your order is now being prepared.',
    'order_confirmed_next_steps' => 'Your order will soon be assigned to a delivery person.',
    'order_paid_at' => 'Paid on',

    // Order Processing
    'order_processing_subject' => 'Order #:order_number Out for Delivery',
    'order_processing_message' => 'Your order **#:order_number** is now out for delivery!',
    'order_processing_footer_message' => 'Your delivery person will contact you soon.',
    'order_processing_delivery_info' => 'Estimated delivery within 2-4 hours depending on delivery type.',
    'order_processing_at' => 'Out for delivery on',
    'delivery_person' => 'Delivery Person',
    'delivery_person_phone' => 'Delivery Person Phone',
    'estimated_delivery' => 'Estimated Delivery',

    // Order Delivered
    'order_delivered_subject' => 'Order #:order_number Delivered!',
    'order_delivered_celebration' => 'Delivery Successful!',
    'order_delivered_message' => 'We are happy to confirm that your order **#:order_number** has been successfully delivered!',
    'order_delivered_footer_message' => 'Thank you for choosing our services!',
    'order_delivered_feedback_message' => 'We value your feedback! Rate your experience in the app.',
    'delivery_details' => 'Delivery Details',
    'delivered_by' => 'Delivered by',
    'feedback_request' => '⭐ Click on the order in the app and give your feedback',
    'thank_you_for_business' => 'Thank you for your business!',

    // Order Cancelled
    'order_cancelled_subject' => 'Order #:order_number Cancelled',
    'order_cancelled_message' => 'We regret to inform you that your order **#:order_number** has been cancelled.',
    'order_cancelled_footer_message' => 'We apologize for any inconvenience caused.',
    'order_cancelled_refund_message' => 'If payment was made, refund will be processed within 3-5 business days.',
    'cancellation_details' => 'Cancellation Details',
    'order_cancelled_at' => 'Cancelled on',
    'cancellation_reason' => 'Cancellation Reason',
    'refund_info' => 'Refund Information',
    'contact_support_message' => 'Our support team is available to assist you.',

    // Order Pending Payment
    'order_pending_subject' => 'Payment Pending - Order #:order_number',
    'order_pending_message' => 'Your order **#:order_number** is pending payment.',
    'order_pending_footer_message' => 'Complete your payment so we can process your order.',
    'order_pending_payment_warning' => 'Your order will be automatically cancelled if payment is not completed within 24 hours.',
    'order_pending_timeout_warning' => 'This order will automatically expire in 24 hours without payment.',
    'payment_details' => 'Payment Details',
    'complete_payment_now' => 'Complete Payment Now',
    'urgent' => 'Urgent',

    // Order Paid
    'order_paid_subject' => 'Payment Confirmed - Order #:order_number',
    'payment_confirmed' => 'Payment Confirmed',
    'order_paid_message' => 'Perfect! Payment for your order **#:order_number** has been confirmed.',
    'order_paid_footer_message' => 'Your order will now be processed quickly.',
    'order_paid_next_steps' => 'Your order will be confirmed and assigned to a delivery person shortly.',
    'payment_confirmation_details' => 'Payment Confirmation Details',
    'amount_paid' => 'Amount Paid',
    'payment_date' => 'Payment Date',
    'payment_receipt' => '🧾 Payment Receipt',
    'keep_receipt_message' => 'Keep this email as receipt of your payment.',

    // Order Payment Failed
    'order_payment_failed_subject' => 'Payment Failed - Order #:order_number',
    'order_payment_failed_message' => 'Payment for your order **#:order_number** has failed.',
    'order_payment_failed_footer_message' => 'Please retry payment to continue with your order.',
    'payment_failure_details' => 'Payment Failure Details',
    'failure_date' => 'Failure Date',
    'possible_reasons' => 'Possible Reasons',
    'insufficient_funds' => 'Insufficient funds',
    'expired_card' => 'Expired card',
    'network_issue' => 'Network issue',
    'bank_decline' => 'Bank decline',
    'retry_payment_now' => 'Retry Payment Now',
    'payment_support_message' => 'Our team can help you with payment issues.',

    // Common elements
    'order_date' => 'Order Date',
    'next_steps' => 'Next Steps',
    'need_help' => 'Need Help?',
    'note' => 'Note',

    // Legacy Order notification emails
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
