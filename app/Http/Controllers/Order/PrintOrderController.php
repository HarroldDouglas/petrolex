<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\PrintOrderRequest;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

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

    /**
     * Download the order invoice as PDF.
     *
     * Route: GET /orders/{order}/download/invoice
     * Name: orders.download.invoice
     */
    public function downloadPdf(int $orderId)
    {
        $orderDetails = $this->orderService->getOrderWithGroupedItems($orderId);

        if (! $orderDetails) {
            abort(SymfonyResponse::HTTP_NOT_FOUND, 'Commande introuvable');
        }

        $data = [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ];

        $pdf = PDF::loadView('orders.print.pdf-invoice', $data);

        $filename = 'facture-'.($orderDetails->order->order_number ?? $orderDetails->order->id).'.pdf';

        return $pdf->download($filename);
    }
}
