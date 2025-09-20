<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PrintOrderRequest;
use App\Services\Invoice\InvoiceService;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PrintOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private InvoiceService $invoiceService,
    ) {}

    /**
     * Print the order ticket.
     *
     * Route: GET /orders/{order}/print
     * Name: orders.print.ticket
     */
    public function __invoke(PrintOrderRequest $request, int $orderId): View
    {
        $orderDetails = $this->orderService->getOrderWithGroupedItems($orderId);

        if (! $orderDetails) {
            abort(Response::HTTP_NOT_FOUND, 'Commande introuvable');
        }

        $withStub = $request->boolean('withStub');

        $view = $withStub ? 'orders.print.ticket-with-stub' : 'orders.print.ticket';

        return view($view, [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);
    }

    /**
     * Download the order invoice as PDF.
     *
     * Route: GET /orders/{order}/download/invoice
     * Name: orders.download.invoice
     */
    public function downloadPdf(int $orderId): BinaryFileResponse
    {
        $invoicePath = $this->invoiceService->getInvoicePath($orderId);

        if (! $invoicePath) {
            Log::critical("Invoice PDF missing for order {$orderId}", [
                'order_id' => $orderId,
                'user_id' => auth()->id(),
            ]);
            abort(SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR, 'Invoice PDF not found - system error');
        }

        $order = \App\Models\Order::findOrFail($orderId);
        $filename = 'facture-'.($order->order_number ?? $order->id).'.pdf';

        return response()->download($invoicePath, $filename);
    }
}
