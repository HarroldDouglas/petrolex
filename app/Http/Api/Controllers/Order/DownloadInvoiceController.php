<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class DownloadInvoiceController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function __invoke(Order $order): Response
    {
        $user = auth()->user();
        if ($user->customer && $user->customer->id !== $order->customer_id) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to download this invoice');
        }

        $orderDetails = $this->orderService->getOrderWithGroupedItems($order->id);

        if (! $orderDetails) {
            abort(Response::HTTP_NOT_FOUND, 'Order not found');
        }

        $pdf = Pdf::loadView('orders.print.pdf-invoice', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);

        $filename = 'facture-'.($order->order_number ?? $order->id).'.pdf';

        return $pdf->download($filename);
    }
}
