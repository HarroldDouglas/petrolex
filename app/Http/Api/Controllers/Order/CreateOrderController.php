<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\CreateOrderWithoutPaymentDTO;
use App\Http\Api\Requests\Order\CreateOrderRequest;
use App\Http\Api\Responses\Order\CreateOrderResponse;
use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/orders",
 *     summary="Créer une nouvelle commande",
 *     description="Crée une nouvelle commande pour le client authentifié. La commande est créée sans paiement et nécessite une étape de paiement séparée.",
 *     operationId="api.orders.store",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Données de la commande à créer",
 *         @OA\JsonContent(
 *             required={"delivery_address_id", "distribution_center_id", "delivery_type", "items", "delivery_fee", "total_amount"},
 *             @OA\Property(
 *                 property="delivery_address_id",
 *                 type="integer",
 *                 description="ID de l'adresse de livraison (doit appartenir au client)",
 *                 example=1
 *             ),
 *             @OA\Property(
 *                 property="distribution_center_id",
 *                 type="integer",
 *                 description="ID du centre de distribution",
 *                 example=1
 *             ),
 *             @OA\Property(
 *                 property="delivery_type",
 *                 type="string",
 *                 description="Type de livraison",
 *                 enum={"immediate", "scheduled"},
 *                 example="immediate"
 *             ),
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *                 description="Articles de la commande (1-50 articles)",
 *                 minItems=1,
 *                 maxItems=50,
 *                 @OA\Items(
 *                     type="object",
 *                     required={"product_category_id", "quantity", "unit_price"},
 *                     @OA\Property(property="product_category_id", type="integer", description="ID de la catégorie de produit", example=1),
 *                     @OA\Property(property="quantity", type="integer", minimum=1, maximum=100, description="Quantité", example=2),
 *                     @OA\Property(property="unit_price", type="number", format="decimal", minimum=0, description="Prix unitaire", example=1500.00),
 *                     @OA\Property(property="option", type="string", enum={"new", "exchange"}, description="Option pour bouteilles", example="new")
 *                 )
 *             ),
 *             @OA\Property(
 *                 property="delivery_fee",
 *                 type="number",
 *                 format="decimal",
 *                 minimum=0,
 *                 description="Frais de livraison",
 *                 example=500.00
 *             ),
 *             @OA\Property(
 *                 property="total_amount",
 *                 type="number",
 *                 format="decimal",
 *                 minimum=0,
 *                 description="Montant total de la commande",
 *                 example=3500.00
 *             ),
 *             @OA\Property(
 *                 property="comments",
 *                 type="string",
 *                 maxLength=500,
 *                 description="Commentaires optionnels",
 *                 example="Livrer avant 18h"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Commande créée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Commande créée avec succès. Procédez au paiement.")
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
 *         description="Non autorisé (utilisateur n'est pas un client)",
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Données de validation invalides",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
final class CreateOrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function __invoke(CreateOrderRequest $request): CreateOrderResponse
    {
        $data = $request->validated();
        $data['customer_id'] = $request->user()->customer->id;

        $orderDTO = CreateOrderWithoutPaymentDTO::from($data);

        $order = $this->orderService->createWithoutPayment($orderDTO);

        $order->load([
            'items.productCategory',
            'deliveryAddress.neighborhood.municipality.city.country',
            'customer.user.country',
            'distributionCenter.neighborhood.municipality.city.country',
            'deliveryPerson.user',
            'payment',
            'refunds',
            'deliveryTracking',
        ]);

        return CreateOrderResponse::withOrder($order);
    }
}
