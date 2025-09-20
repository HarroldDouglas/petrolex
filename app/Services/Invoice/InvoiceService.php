<?php

namespace App\Services\Invoice;

use App\Models\Order;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Generate and store invoice PDF for an order
     */
    public function generateAndStoreInvoicePdf(Order $order): string
    {
        try {
            $orderDetails = $this->orderService->getOrderWithGroupedItems($order->id);

            if (! $orderDetails) {
                throw new \Exception("Order details not found for order {$order->id}");
            }

            $data = [
                'order' => $orderDetails->order,
                'groupedItems' => $orderDetails->groupedItems,
            ];

            $pdf = PDF::loadView('orders.print.pdf-invoice', $data);
            $pdfContent = $pdf->output();

            $filePath = $this->getStoragePath($order);
            $this->ensureDirectoryExists($order->customer_id);

            Storage::disk('local')->put($filePath, $pdfContent);

            Log::info("Invoice PDF generated successfully for order {$order->id}", [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'file_path' => $filePath,
            ]);

            return $filePath;
        } catch (\Exception $e) {
            Log::critical("Failed to generate invoice PDF for order {$order->id}", [
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get the full storage path for an order's invoice
     */
    public function getInvoicePath(int $orderId): ?string
    {
        $order = Order::find($orderId);

        if (! $order) {
            return null;
        }

        $filePath = $this->getStoragePath($order);

        if (! Storage::disk('local')->exists($filePath)) {
            Log::warning("Invoice PDF not found for order {$orderId}", [
                'order_id' => $orderId,
                'expected_path' => $filePath,
            ]);

            return null;
        }

        return Storage::disk('local')->path($filePath);
    }

    /**
     * Get the download URL for an order's invoice
     */
    public function getInvoiceUrl(int $orderId): ?string
    {
        $path = $this->getInvoicePath($orderId);

        return $path ? route('orders.download.invoice', ['order' => $orderId]) : null;
    }

    /**
     * Check if invoice exists for an order
     */
    public function invoiceExists(int $orderId): bool
    {
        return $this->getInvoicePath($orderId) !== null;
    }

    /**
     * Get the relative storage path for an order's invoice
     */
    private function getStoragePath(Order $order): string
    {
        $filename = 'facture-'.($order->order_number ?? $order->id).'.pdf';

        return "invoices/{$order->customer_id}/{$filename}";
    }

    /**
     * Ensure the customer's invoice directory exists
     */
    private function ensureDirectoryExists(int $customerId): void
    {
        $directory = "invoices/{$customerId}";

        if (! Storage::disk('local')->exists($directory)) {
            Storage::disk('local')->makeDirectory($directory);
        }
    }

    /**
     * Regenerate invoice PDF if it already exists
     */
    public function regenerateInvoicePdf(Order $order): string
    {
        $filePath = $this->getStoragePath($order);

        // Delete existing file if it exists
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
            Log::info('Deleted existing invoice PDF for regeneration', [
                'order_id' => $order->id,
                'file_path' => $filePath,
            ]);
        }

        return $this->generateAndStoreInvoicePdf($order);
    }
}
