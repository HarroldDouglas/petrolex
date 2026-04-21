<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/orders/{order}/download/invoice",
 *     summary="Télécharger la facture PDF d'une commande",
 *     description="Génère et télécharge la facture PDF d'une commande spécifique. Le PDF est généré à la demande avec tous les détails de la commande.",
 *     operationId="api.orders.download.invoice",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID de la commande",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Facture PDF générée et téléchargée avec succès",
 *
 *         @OA\MediaType(
 *             mediaType="application/pdf",
 *
 *             @OA\Schema(type="string", format="binary")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Non autorisé à télécharger cette facture",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Commande non trouvée",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 *
 * TODO: Déplacer cette documentation vers documentation/Order/DownloadInvoiceControllerDoc.php
 * une fois que le système de scan de documentation sera corrigé
 */
class DownloadInvoiceController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Download order invoice.
     *
     * Route: GET /api/orders/{order}/download/invoice
     * Name: api.orders.download.invoice
     */
    public function __invoke(Order $order): Response
    {
        $user = auth()->user();

        $isOrderOwner = $user->customer && $user->customer->id === $order->customer_id;
        $isAdmin = $user->hasAnyRole(['admin', 'manager', 'center_manager']);
        $isDeliveryPerson = $user->hasRole('delivery_person');

        if (! $isOrderOwner && ! $isAdmin && ! $isDeliveryPerson) {
            abort(Response::HTTP_FORBIDDEN, __('api.order_invoice_not_belongs_to_you'));
        }

        $filename = 'facture-'.($order->order_number ?? $order->id).'.pdf';
        $cachePath = 'invoices/'.$order->id.'_'.$order->updated_at->timestamp.'.pdf';

        if (Storage::disk('local')->exists($cachePath)) {
            $pdfContent = Storage::disk('local')->get($cachePath);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        set_time_limit(120);

        $orderDetails = $this->orderService->getOrderWithGroupedItems($order->id);

        if (! $orderDetails) {
            abort(Response::HTTP_NOT_FOUND, 'Order not found');
        }

        $pdf = Pdf::loadView('orders.print.pdf-invoice', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);

        $pdfContent = $pdf->output();

        Storage::disk('local')->put($cachePath, $pdfContent);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
