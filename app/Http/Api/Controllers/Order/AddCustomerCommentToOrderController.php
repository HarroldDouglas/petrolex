<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\AddCustomerCommentToOrderDTO;
use App\Http\Api\Requests\Order\AddCustomerCommentToOrderRequest;
use App\Http\Api\Responses\Order\OrderDetailsResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/orders/{order}/customer-feedback",
 *     summary="Ajouter un commentaire client à une commande",
 *     description="Permet au client d'ajouter un commentaire et une note à une commande livrée ou annulée.",
 *     operationId="api.orders.customer-feedback",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID de la commande",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Commentaire et note du client",
 *         @OA\JsonContent(
 *             required={"comments", "rating"},
 *             @OA\Property(
 *                 property="comments",
 *                 type="string",
 *                 minLength=10,
 *                 description="Commentaire du client (minimum 10 caractères)",
 *                 example="Excellente livraison, produit de qualité et livreur très professionnel."
 *             ),
 *             @OA\Property(
 *                 property="rating",
 *                 type="number",
 *                 format="decimal",
 *                 minimum=1,
 *                 maximum=5,
 *                 description="Note de 1 à 5",
 *                 example=4.5
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Commentaire ajouté avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Commentaire ajouté avec succès")
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
 *         description="Cette commande ne vous appartient pas",
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
 *         description="Vous ne pouvez laisser un avis que sur des commandes livrées ou annulées",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class AddCustomerCommentToOrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    /**
     * Add customer feedback to the specified order.
     *
     * Route: POST /{order}/customer-feedback
     * Name: orders.customer-feedback
     * Documentation: See documentation/Order/AddCustomerCommentToOrderDoc.php
     */
    public function __invoke(AddCustomerCommentToOrderRequest $request, Order $order): OrderDetailsResponse
    {
        // Security check: Ensure the order belongs to the authenticated customer
        $authenticatedUser = $request->user();
        if (! $authenticatedUser->customer || $order->customer_id !== $authenticatedUser->customer->id) {
            abort(403, 'Cette commande ne vous appartient pas.');
        }

        // Business rule: Only delivered or cancelled orders can receive feedback
        $allowedStatuses = [\App\Enums\OrderStatus::DELIVERED()->value, \App\Enums\OrderStatus::CANCELLED()->value];
        if (! in_array($order->status, $allowedStatuses)) {
            abort(422, 'Vous ne pouvez laisser un avis que sur des commandes livrées ou annulées.');
        }

        $dto = new AddCustomerCommentToOrderDTO(
            comments: $request->input('comments'),
            rating: (float) $request->input('rating'),
        );

        $order = $this->orderService->update($order, $dto->toArrayFiltered());

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

        return OrderDetailsResponse::withOrder($order, 'Commentaire ajouté à la commande avec succès');
    }
}
