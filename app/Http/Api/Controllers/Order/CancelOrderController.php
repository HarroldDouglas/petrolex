<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Enums\OrderStatus;
use App\Http\Api\Responses\Order\CancelOrderResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\CancelOrderRequest;
use App\Models\Order;
use App\Services\Order\OrderService;
use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/api/orders/{order}/cancel",
 *     summary="Annuler une commande",
 *     description="Marque une commande comme annulée. Seules les commandes en attente ou payées peuvent être annulées.",
 *     operationId="api.orders.cancel",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID de la commande à annuler",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=false,
 *         description="Détails de l'annulation (optionnel)",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="cancelled_reason",
 *                 type="string",
 *                 description="Raison de l'annulation",
 *                 example="Le client a changé d'avis"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Commande annulée avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Commande annulée avec succès")
 *             ),
 *             @OA\Property(property="data", ref="#/components/schemas/OrderDetailsData")
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
 *         description="Cette commande ne vous appartient pas",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Commande non trouvée",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Cette commande ne peut plus être annulée",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class CancelOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * Cancel the specified order.
     *
     * Route: PATCH /orders/{order}/cancel
     * Name: orders.cancel
     */
    public function __invoke(CancelOrderRequest $request, Order $order): CancelOrderResponse
    {
        $authenticatedUser = $request->user();
        if (! $authenticatedUser->customer || $order->customer_id !== $authenticatedUser->customer->id) {
            abort(403, __('api.order_not_belongs_to_you'));
        }

        $allowedStatuses = [OrderStatus::PENDING()->value, OrderStatus::PAID()->value];
        if (! in_array($order->status, $allowedStatuses)) {
            abort(422, __('api.order_cannot_be_cancelled'));
        }

        $data = [
            'cancelled_reason' => $request->input('cancelled_reason', 'Annulée par le client'),
            'cancelled_by' => $authenticatedUser->id,
            'status' => OrderStatus::CANCELLED(),
            'cancelled_at' => now(),
        ];

        /** @var Order $updatedOrder */
        $updatedOrder = $this->orderService->update($order, $data);

        return CancelOrderResponse::withOrder($updatedOrder->loadDetailRelations());
    }
}
