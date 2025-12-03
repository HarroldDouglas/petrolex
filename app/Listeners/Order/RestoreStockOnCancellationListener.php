<?php

namespace App\Listeners\Order;

use App\Enums\OrderStatus;
use App\Enums\ProductType;
use App\Events\OrderStatusChanged;
use App\Listeners\BaseListener;
use App\Models\ProductCategoryDistributionCenter;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RestoreStockOnCancellationListener extends BaseListener
{
    public function __construct(
        private WalletService $walletService
    ) {}

    /**
     * Get unique identifiers for this event
     *
     * @param  OrderStatusChanged  $event
     * @return array{order_id: int, old_status: string|null, new_status: string, event_type: string}
     */
    protected function getEventIdentifiers($event): array
    {
        return [
            'order_id' => $event->order->id,
            'old_status' => $event->oldStatus?->value,
            'new_status' => $event->newStatus->value,
            'event_type' => 'order_cancellation_stock_restore',
        ];
    }

    /**
     * Handle the event - Restore stock and credit wallet when order is cancelled
     *
     * @param  OrderStatusChanged  $event
     */
    protected function handleEvent($event): void
    {
        /** @var OrderStatusChanged $event */

        // Only restore stock when order becomes CANCELLED
        if ($event->newStatus->value !== OrderStatus::CANCELLED()->value) {
            return;
        }

        $order = $event->order;

        // Only restore stock if order was previously PAID
        // (no stock to restore if order was never paid)
        if ($event->oldStatus?->value !== OrderStatus::PAID()->value) {
            Log::info('Order cancelled but was not paid, no stock to restore', [
                'order_id' => $order->id,
                'old_status' => $event->oldStatus?->value,
            ]);

            return;
        }

        DB::transaction(function () use ($order) {
            // 1. Restore stock
            $this->restoreStock($order);

            // 2. Credit customer wallet with full traceability
            $this->creditCustomerWallet($order);
        });

        Log::info('Stock restored and customer wallet credited for cancelled order', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'total_amount' => $order->total_amount,
        ]);
    }

    /**
     * Restore stock for all order items
     */
    private function restoreStock($order): void
    {
        $orderItems = $order->items()->with('productCategory')->get();

        foreach ($orderItems as $item) {
            $stockRecord = ProductCategoryDistributionCenter::where('product_category_id', $item->product_category_id)
                ->where('distribution_center_id', $order->distribution_center_id)
                ->lockForUpdate()
                ->first();

            if (! $stockRecord) {
                Log::warning('Stock record not found for product category when restoring', [
                    'product_category_id' => $item->product_category_id,
                    'distribution_center_id' => $order->distribution_center_id,
                ]);
                continue;
            }

            $productType = $item->productCategory->product_type;

            if ($productType === ProductType::BOTTLE()) {
                // For bottles, restore stock_filled
                $newStockFilled = $stockRecord->stock_filled + $item->quantity;
                $stockRecord->update(['stock_filled' => $newStockFilled]);

                Log::info('Restored bottle stock', [
                    'product_category_id' => $item->product_category_id,
                    'quantity' => $item->quantity,
                    'old_stock_filled' => $stockRecord->stock_filled,
                    'new_stock_filled' => $newStockFilled,
                ]);
            } elseif ($productType === ProductType::ACCESSORY()) {
                // For accessories, restore stock
                $newStock = $stockRecord->stock + $item->quantity;
                $stockRecord->update(['stock' => $newStock]);

                Log::info('Restored accessory stock', [
                    'product_category_id' => $item->product_category_id,
                    'quantity' => $item->quantity,
                    'old_stock' => $stockRecord->stock,
                    'new_stock' => $newStock,
                ]);
            }
        }
    }

    /**
     * Credit customer wallet with the order total amount using WalletService
     */
    private function creditCustomerWallet($order): void
    {
        $customer = $order->customer;

        if (! $customer) {
            Log::error('Cannot credit wallet: customer not found for order', [
                'order_id' => $order->id,
            ]);

            return;
        }

        $refundAmount = (float) $order->total_amount;

        // Use WalletService for full traceability
        $transaction = $this->walletService->refundOrder($customer, $order);

        // Update order's total_refunded_amount
        $order->update([
            'total_refunded_amount' => $refundAmount,
        ]);

        Log::info('Customer wallet credited for cancelled order via WalletService', [
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'amount_credited' => $refundAmount,
            'transaction_reference' => $transaction->reference,
            'balance_before' => $transaction->balance_before,
            'balance_after' => $transaction->balance_after,
        ]);

        // Send notification to customer about the refund
        $this->sendRefundNotification($customer, $order, $transaction->balance_before, $transaction->balance_after);
    }

    /**
     * Send notification to customer about the refund
     */
    private function sendRefundNotification($customer, $order, $previousBalance, $newBalance): void
    {
        try {
            $user = $customer->user;
            if ($user && $user->email) {
                $user->notify(new \App\Notifications\OrderCancelledRefundNotification(
                    $order,
                    $order->total_amount,
                    $newBalance
                ));
                Log::info('Refund notification sent to customer', [
                    'customer_id' => $customer->id,
                    'user_id' => $user->id,
                    'email' => $user->email,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send refund notification', [
                'customer_id' => $customer->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
