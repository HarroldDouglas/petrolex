<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PrintOrderRequest;
use App\Models\Order;
use App\Services\Order\InvoicePdfService;
use App\Services\Order\OrderService;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PrintOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private InvoicePdfService $invoicePdfService,
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

    public function downloadPdf(int $orderId): Response
    {
        $order = Order::find($orderId);

        if (! $order) {
            abort(Response::HTTP_NOT_FOUND, 'Commande introuvable');
        }

        $pdfContent = $this->invoicePdfService->getContent($order);
        $filename = 'facture-'.($order->order_number ?? $order->id).'.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
