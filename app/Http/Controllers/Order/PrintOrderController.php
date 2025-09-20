<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PrintOrderRequest;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PrintOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
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
        $orderDetails = $this->orderService->getOrderWithGroupedItems($orderId);

        if (! $orderDetails) {
            abort(Response::HTTP_NOT_FOUND, 'Commande introuvable');
        }

        $pdf = Pdf::loadView('orders.print.pdf-invoice', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);

        $filename = 'facture-'.($orderDetails->order->order_number ?? $orderDetails->order->id).'.pdf';

        return $pdf->download($filename);
    }
}
