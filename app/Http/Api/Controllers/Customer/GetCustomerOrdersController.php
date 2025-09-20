<?php

namespace App\Http\Api\Controllers\Customer;

use App\DTOs\Order\GetOrdersFilterDTO;
use App\Http\Api\Requests\Customer\GetCustomerOrdersRequest;
use App\Http\Api\Responses\Customer\CustomerOrdersResponse;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\Customer\CustomerService;
use Illuminate\Support\Arr;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/my/orders",
 *     summary="Récupérer les commandes du client connecté",
 *     description="Retourne la liste des commandes du client connecté avec possibilité de filtrage et pagination.",
 *     operationId="api.my.orders.index",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Nombre d'éléments par page (défaut: 10)",
 *
 *         @OA\Schema(type="integer", example=10, minimum=1, maximum=100)
 *     ),
 *
 *     @OA\Parameter(
 *         name="order_number",
 *         in="query",
 *         required=false,
 *         description="Filtrer par numéro de commande (recherche partielle)",
 *
 *         @OA\Schema(type="string", example="CMD-202412-0001")
 *     ),
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filtrer par statut de commande",
 *
 *         @OA\Schema(type="string", enum={"pending", "confirmed", "in_progress", "delivered", "cancelled", "paid", "failed"}, example="confirmed")
 *     ),
 *
 *     @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         required=false,
 *         description="Filtrer par type de livraison",
 *
 *         @OA\Schema(type="string", enum={"normal", "fast"}, example="normal")
 *     ),
 *
 *     @OA\Parameter(
 *         name="payment_method",
 *         in="query",
 *         required=false,
 *         description="Filtrer par méthode de paiement",
 *
 *         @OA\Schema(type="string", enum={"orange_money", "mtn_money", "credit_card"}, example="orange_money")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Liste des commandes récupérée avec succès",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Commandes récupérées avec succès")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *
 *                 @OA\Items(ref="#/components/schemas/OrderData")
 *             ),
 *
 *             @OA\Property(
 *                 property="links",
 *                 type="object",
 *                 @OA\Property(property="first", type="string", nullable=true),
 *                 @OA\Property(property="last", type="string", nullable=true),
 *                 @OA\Property(property="prev", type="string", nullable=true),
 *                 @OA\Property(property="next", type="string", nullable=true)
 *             ),
 *             @OA\Property(
 *                 property="meta",
 *                 type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="from", type="integer", example=1),
 *                 @OA\Property(property="last_page", type="integer", example=5),
 *                 @OA\Property(property="per_page", type="integer", example=10),
 *                 @OA\Property(property="to", type="integer", example=10),
 *                 @OA\Property(property="total", type="integer", example=42)
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
 *         description="Accès refusé - rôle client requis",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 *
 * TODO: Déplacer cette documentation vers documentation/Customer/GetCustomerOrdersControllerDoc.php
 * une fois que le système de scan de documentation sera corrigé
 */
class GetCustomerOrdersController extends Controller
{
    public function __construct(
        protected CustomerService $customerService
    ) {}

    /**
     * Get customer orders.
     *
     * Route: GET /my/orders
     * Name: api.my.orders.index
     */
    public function __invoke(GetCustomerOrdersRequest $request): CustomerOrdersResponse
    {
        $user = $request->user();
        $customer = $user->customer;
        $validated = $request->validated();
        $filters = GetOrdersFilterDTO::from(Arr::except($validated, ['per_page']));
        $perPage = $validated['per_page'] ?? 10;

        $orders = $this->customerService->getOrders($customer, $filters, $perPage);

        return CustomerOrdersResponse::paginatedCollection($orders);
    }
}
