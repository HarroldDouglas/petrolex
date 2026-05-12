<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
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
        $tGlobal = microtime(true);
        $orderId = $order->id;

        $t0 = microtime(true);
        $user = auth()->user();

        $isOrderOwner = $user->customer && $user->customer->id === $order->customer_id;
        $isAdmin = $user->hasAnyRole(['admin', 'manager', 'center_manager']);
        $isDeliveryPerson = $user->hasRole('delivery_person');

        if (! $isOrderOwner && ! $isAdmin && ! $isDeliveryPerson) {
            abort(Response::HTTP_FORBIDDEN, __('api.order_invoice_not_belongs_to_you'));
        }
        Log::info('TIMING invoice.auth_check', ['order_id' => $orderId, 'ms' => round((microtime(true) - $t0) * 1000, 2)]);

        $filename = 'facture-'.($order->order_number ?? $order->id).'.pdf';
        $cachePath = 'invoices/'.$order->id.'_'.$order->updated_at->timestamp.'.pdf';

        $t0 = microtime(true);
        $cacheExists = Storage::disk('local')->exists($cachePath);
        Log::info('TIMING invoice.cache_check', ['order_id' => $orderId, 'ms' => round((microtime(true) - $t0) * 1000, 2), 'hit' => $cacheExists]);

        if ($cacheExists) {
            $t0 = microtime(true);
            $pdfContent = Storage::disk('local')->get($cachePath);
            Log::info('TIMING invoice.cache_read', ['order_id' => $orderId, 'ms' => round((microtime(true) - $t0) * 1000, 2), 'size_kb' => round(strlen($pdfContent) / 1024, 2)]);
            Log::info('TIMING invoice.TOTAL', ['order_id' => $orderId, 'ms' => round((microtime(true) - $tGlobal) * 1000, 2), 'path' => 'cache_hit']);

            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        set_time_limit(120);

        $t0 = microtime(true);
        $orderDetails = $this->orderService->getOrderWithGroupedItems($order->id);
        Log::info('TIMING invoice.db_fetch', ['order_id' => $orderId, 'ms' => round((microtime(true) - $t0) * 1000, 2)]);

        if (! $orderDetails) {
            abort(Response::HTTP_NOT_FOUND, 'Order not found');
        }

        $t0 = microtime(true);
        $pdf = Pdf::loadView('orders.print.pdf-invoice', [
            'order' => $orderDetails->order,
            'groupedItems' => $orderDetails->groupedItems,
        ]);
        Log::info('TIMING invoice.pdf_loadview', ['order_id' => $orderId, 'ms' => round((microtime(true) - $t0) * 1000, 2)]);

        $t0 = microtime(true);
        $pdfContent = $pdf->output();
        Log::info('TIMING invoice.pdf_output', ['order_id' => $orderId, 'ms' => round((microtime(true) - $t0) * 1000, 2), 'size_kb' => round(strlen($pdfContent) / 1024, 2)]);

        $t0 = microtime(true);
        Storage::disk('local')->put($cachePath, $pdfContent);
        Log::info('TIMING invoice.cache_write', ['order_id' => $orderId, 'ms' => round((microtime(true) - $t0) * 1000, 2)]);

        Log::info('TIMING invoice.TOTAL', ['order_id' => $orderId, 'ms' => round((microtime(true) - $tGlobal) * 1000, 2), 'path' => 'cache_miss']);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
