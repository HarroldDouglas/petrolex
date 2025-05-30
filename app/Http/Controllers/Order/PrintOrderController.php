<?php

namespace App\Http\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;

class PrintOrderController extends Controller
{
    /**
     * Afficher le ticket d'impression simple
     */
    public function printTicket(Order $order)
    {
        // just for mock-up, we will get the first order with status PROCESSING or COMPLETED
        $order = $this->fakeOrderForDemo();

        return view('orders.print.ticket', compact('order'));
    }

    private function fakeOrderForDemo(): Order
    {
        // This method is just a placeholder for the mock-up.
        // In a real application, you would fetch the order from the database.
        return Order::where('status', OrderStatus::PROCESSING())
            ->orWhere('status', OrderStatus::DELIVERED())
            ->first()
            ->load([
                'customer',
                'deliveryAddress',
                'distributionCenter',
                'deliveryPerson',
                'items.product',
                'items.product.bottle',
                'items.product.accessory',
            ]);
    }

    /**
     * Afficher le ticket d'impression avec souche
     */
    public function printTicketWithStub(Order $order)
    {
        $order = $this->fakeOrderForDemo();

        return view('orders.print.ticket-with-stub', compact('order'));
    }

    /**
     * Générer le PDF du ticket (optionnel)
     */
    public function downloadPdf(Order $order)
    {
        $order = $this->fakeOrderForDemo();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('orders.print.ticket-with-stub', compact('order'));

        return $pdf->download('commande-'.$order->order_number.'.pdf');
    }
}
