<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\Http\Api\Responses\Order\OrderDetailsResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Patch(
 *     path="/api/orders/{order}/deliver",
 *     summary="Marquer une commande comme livrée",
 *     description="Marque une commande comme livrée. Seul le livreur assigné à cette commande peut effectuer cette action.",
 *     operationId="api.orders.deliver",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID de la commande à marquer comme livrée",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Commande marquée comme livrée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Commande marquée comme livrée avec succès")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="order", ref="#/components/schemas/OrderDetailsData")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non authentifié",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=403,
 *         description="Vous n'êtes pas autorisé à marquer cette commande comme livrée",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Commande non trouvée",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Cette commande ne peut pas être marquée comme livrée",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
final class DeliverOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Mark an order as delivered.
     *
     * Route: PATCH /api/orders/{order}/deliver
     * Name: api.orders.deliver
     */
    public function __invoke(Request $request, Order $order): OrderDetailsResponse
    {
        $user = $request->user();

        // Security check: Only the delivery person assigned to this order can mark it as delivered
        $isAssignedDeliveryPerson = $user->deliveryPerson && $order->delivery_person_id === $user->deliveryPerson->id;

        if (! $isAssignedDeliveryPerson) {
            abort(403, 'Vous n\'êtes pas autorisé à marquer cette commande comme livrée.');
        }

        $updatedOrder = $this->orderService->deliverOrder($order);

        if (empty($updatedOrder)) {
            abort(422, 'This order cannot be marked as delivered.');
        }

        // Load all relations like GetOrderDetailsController
        $order->load([
            'customer.user.country',
            'customer.deliveryAddresses.neighborhood.municipality.city.country',
            'deliveryAddress.neighborhood.municipality.city.country',
            'deliveryPerson.user',
            'distributionCenter',
            'payment',
            'items.productCategory',
            'refunds',
            'deliveryTracking',
        ]);

        return OrderDetailsResponse::withOrder($order, 'Commande marquée comme livrée avec succès');
    }
}
