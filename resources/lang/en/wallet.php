<?php

return [
    // Transaction types
    'transaction_credit' => 'Credit',
    'transaction_debit' => 'Debit',

    // Descriptions
    'credit_description' => 'Wallet credit',
    'debit_description' => 'Wallet debit',
    'order_payment' => 'Order payment #:order_number',
    'order_refund' => 'Order refund #:order_number',

    // Balance
    'current_balance' => 'Current balance',
    'balance_before' => 'Balance before',
    'balance_after' => 'Balance after',
    'insufficient_balance' => 'Insufficient balance',

    // Payment breakdown
    'payment_breakdown' => 'Payment breakdown',
    'total_amount' => 'Total amount',
    'wallet_amount' => 'Wallet amount',
    'remaining_amount' => 'Remaining amount to pay',
    'wallet_used' => 'Wallet used',
    'external_payment' => 'External payment required',

    // Messages
    'balance_credited' => 'Your wallet has been credited :amount',
    'balance_debited' => 'Your wallet has been debited :amount',
    'payment_with_wallet' => 'Payment made with wallet',
    'partial_wallet_payment' => 'Partial payment with wallet',
    'payment_failed_refund' => 'Refund - payment failed for order #:order_number',

    // Transaction history
    'transaction_history' => 'Transaction history',
    'no_transactions' => 'No transactions',
];
