<?php

namespace App\Http\Api\Controllers\Order;

use App\Http\Api\Responses\Order\OrderDetailsResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/orders/{order}",
 *     summary="Obtenir les détails d'une commande",
 *     description="Récupère les détails complets d'une commande spécifique. Seul le propriétaire de la commande (client) ou les livreurs peuvent accéder aux détails.",
 *     operationId="api.orders.show",
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
 *         description="Détails de la commande récupérés avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Détails de la commande récupérés avec succès")
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
 *     )
 * )
 */
class GetOrderDetailsController extends Controller
{
    /**
     * Get order details.
     *
     * Route: GET /api/orders/{order}
     * Name: api.orders.show
     */
    public function __invoke(Request $request, Order $order): OrderDetailsResponse
    {
        $authenticatedUser = $request->user();
        $isOrderOwner = $authenticatedUser->customer && $authenticatedUser->customer->id === $order->customer_id;
        $isDeliveryPerson = $authenticatedUser->hasRole('delivery_person');

        if (! $isOrderOwner && ! $isDeliveryPerson) {
            abort(403, __('api.order_not_belongs_to_you'));
        }

        $order->loadDetailRelations();

        return OrderDetailsResponse::withOrder($order);
    }
}
