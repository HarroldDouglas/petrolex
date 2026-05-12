<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Order;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(private int $orderId) {}

    public function handle(OrderService $orderService): void
    {
        $tGlobal = microtime(true);

        $order = Order::find($this->orderId);
        if (! $order) {
            Log::warning('GenerateInvoicePdfJob: order not found', ['order_id' => $this->orderId]);

            return;
        }

        $cachePath = 'invoices/'.$order->id.'_'.$order->updated_at->timestamp.'.pdf';

        if (Storage::disk('local')->exists($cachePath)) {
            Log::info('GenerateInvoicePdfJob: cache already exists, skipping', [
                'order_id' => $order->id,
                'path' => $cachePath,
            ]);

            return;
        }

        $orderDetails = $orderService->getOrderWithGroupedItems($order->id);

        if (! $orderDetails) {
            Log::warning('GenerateInvoicePdfJob: order details not loadable', ['order_id' => $order->id]);

            return;
        }

        $pdf = Pdf::loadView('orders.print.pdf-invoice', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);

        $pdfContent = $pdf->output();

        Storage::disk('local')->put($cachePath, $pdfContent);

        Log::info('GenerateInvoicePdfJob: invoice pre-generated', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'path' => $cachePath,
            'size_kb' => round(strlen($pdfContent) / 1024, 2),
            'duration_ms' => round((microtime(true) - $tGlobal) * 1000, 2),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('GenerateInvoicePdfJob failed', [
            'order_id' => $this->orderId,
            'error' => $e->getMessage(),
        ]);
    }
}
