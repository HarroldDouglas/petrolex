<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoicePdfService
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function cachePath(Order $order): string
    {
        return 'invoices/'.$order->id.'_'.$order->updated_at->timestamp.'.pdf';
    }

    public function getOrGenerate(Order $order): string
    {
        $path = $this->cachePath($order);

        if (Storage::disk('local')->exists($path)) {
            return $path;
        }

        $tGlobal = microtime(true);
        $orderDetails = $this->orderService->getOrderWithGroupedItems($order->id);

        if (! $orderDetails) {
            throw new \RuntimeException("Order details not loadable for order {$order->id}");
        }

        set_time_limit(120);

        $pdf = Pdf::loadView('orders.print.pdf-invoice', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);

        $pdfContent = $pdf->output();
        Storage::disk('local')->put($path, $pdfContent);

        Log::info('InvoicePdfService: generated', [
            'order_id' => $order->id,
            'path' => $path,
            'size_kb' => round(strlen($pdfContent) / 1024, 2),
            'duration_ms' => round((microtime(true) - $tGlobal) * 1000, 2),
        ]);

        return $path;
    }

    public function getContent(Order $order): string
    {
        $path = $this->getOrGenerate($order);

        return Storage::disk('local')->get($path);
    }
}
