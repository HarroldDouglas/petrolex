<?php

namespace App\Services\Wallet;

use App\Enums\WalletTransactionType;
use App\Models\Customer;
use App\Models\Order;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Get customer's current wallet balance.
     */
    public function getBalance(Customer $customer): float
    {
        return (float) $customer->current_balance;
    }

    /**
     * Check if customer has sufficient balance for an amount.
     */
    public function hasSufficientBalance(Customer $customer, float $amount): bool
    {
        return $this->getBalance($customer) >= $amount;
    }

    /**
     * Calculate payment breakdown for an order.
     *
     * @return array{
     *     total_amount: float,
     *     wallet_amount: float,
     *     payment_amount: float,
     *     wallet_sufficient: bool,
     *     wallet_balance_before: float,
     *     wallet_balance_after: float
     * }
     */
    public function calculatePaymentBreakdown(Customer $customer, float $totalAmount): array
    {
        $walletBalance = $this->getBalance($customer);
        $walletSufficient = $walletBalance >= $totalAmount;

        $walletAmount = $walletSufficient ? $totalAmount : $walletBalance;
        $paymentAmount = $totalAmount - $walletAmount;

        return [
            'total_amount' => $totalAmount,
            'wallet_amount' => $walletAmount,
            'payment_amount' => $paymentAmount,
            'wallet_sufficient' => $walletSufficient,
            'wallet_balance_before' => $walletBalance,
            'wallet_balance_after' => $walletBalance - $walletAmount,
        ];
    }

    /**
     * Credit the customer's wallet.
     */
    public function credit(
        Customer $customer,
        float $amount,
        ?Order $order = null,
        ?string $description = null,
        ?array $metadata = null
    ): WalletTransaction {
        return DB::transaction(function () use ($customer, $amount, $order, $description, $metadata) {
            // Lock the customer row for update to prevent race conditions
            $customer = Customer::lockForUpdate()->find($customer->id);

            $balanceBefore = (float) $customer->current_balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update customer balance
            $customer->update(['current_balance' => $balanceAfter]);

            // Create transaction record
            $transaction = WalletTransaction::create([
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'type' => WalletTransactionType::CREDIT()->value,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => WalletTransaction::generateReference(),
                'description' => $description ?? __('wallet.credit_description'),
                'metadata' => $metadata,
            ]);

            Log::info('Wallet credited', [
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'transaction_reference' => $transaction->reference,
            ]);

            return $transaction;
        });
    }

    /**
     * Debit the customer's wallet.
     *
     * @throws \Exception If insufficient balance
     */
    public function debit(
        Customer $customer,
        float $amount,
        ?Order $order = null,
        ?string $description = null,
        ?array $metadata = null,
        bool $allowNegative = false
    ): WalletTransaction {
        return DB::transaction(function () use ($customer, $amount, $order, $description, $metadata, $allowNegative) {
            // Lock the customer row for update
            $customer = Customer::lockForUpdate()->find($customer->id);

            $balanceBefore = (float) $customer->current_balance;

            if (! $allowNegative && $balanceBefore < $amount) {
                throw new \Exception(__('wallet.insufficient_balance'));
            }

            $balanceAfter = $balanceBefore - $amount;

            // Update customer balance
            $customer->update(['current_balance' => $balanceAfter]);

            // Create transaction record
            $transaction = WalletTransaction::create([
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'type' => WalletTransactionType::DEBIT()->value,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => WalletTransaction::generateReference(),
                'description' => $description ?? __('wallet.debit_description'),
                'metadata' => $metadata,
            ]);

            Log::info('Wallet debited', [
                'customer_id' => $customer->id,
                'order_id' => $order?->id,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'transaction_reference' => $transaction->reference,
            ]);

            return $transaction;
        });
    }

    /**
     * Process payment using wallet and/or external payment method.
     *
     * @return array{
     *     wallet_transaction: WalletTransaction|null,
     *     wallet_amount_used: float,
     *     external_payment_required: bool,
     *     external_payment_amount: float,
     *     payment_breakdown: array
     * }
     */
    public function processOrderPayment(Customer $customer, Order $order, bool $useWallet = true): array
    {
        $totalAmount = (float) $order->total_amount;
        $breakdown = $this->calculatePaymentBreakdown($customer, $totalAmount);

        $walletTransaction = null;
        $walletAmountUsed = 0;

        if ($useWallet && $breakdown['wallet_amount'] > 0) {
            $walletTransaction = $this->debit(
                $customer,
                $breakdown['wallet_amount'],
                $order,
                __('wallet.order_payment', ['order_number' => $order->order_number]),
                [
                    'order_number' => $order->order_number,
                    'total_order_amount' => $totalAmount,
                    'wallet_amount' => $breakdown['wallet_amount'],
                    'external_amount' => $breakdown['payment_amount'],
                ]
            );
            $walletAmountUsed = $breakdown['wallet_amount'];
        }

        return [
            'wallet_transaction' => $walletTransaction,
            'wallet_amount_used' => $walletAmountUsed,
            'external_payment_required' => $breakdown['payment_amount'] > 0,
            'external_payment_amount' => $breakdown['payment_amount'],
            'payment_breakdown' => $breakdown,
        ];
    }

    /**
     * Refund an order to the customer's wallet.
     */
    public function refundOrder(Customer $customer, Order $order, ?float $amount = null): WalletTransaction
    {
        $refundAmount = $amount ?? (float) $order->total_amount;

        return $this->credit(
            $customer,
            $refundAmount,
            $order,
            __('wallet.order_refund', ['order_number' => $order->order_number]),
            [
                'order_number' => $order->order_number,
                'original_amount' => $order->total_amount,
                'refund_amount' => $refundAmount,
                'refund_reason' => 'order_cancelled',
            ]
        );
    }

    /**
     * Refund only the wallet amount used for an order (for partial wallet payments).
     */
    public function refundOrderWallet(Customer $customer, Order $order, float $walletAmountUsed): WalletTransaction
    {
        return $this->credit(
            $customer,
            $walletAmountUsed,
            $order,
            __('wallet.order_wallet_refund', ['order_number' => $order->order_number]),
            [
                'order_number' => $order->order_number,
                'order_total_amount' => $order->total_amount,
                'wallet_amount_refunded' => $walletAmountUsed,
                'refund_reason' => 'order_cancelled_wallet_refund',
            ]
        );
    }

    /**
     * Get transaction history for a customer.
     */
    public function getTransactionHistory(Customer $customer, int $perPage = 15)
    {
        return WalletTransaction::where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
