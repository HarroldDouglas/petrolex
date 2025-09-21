<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Order;

use App\DTOs\Order\ScanEmptyBottleDTO;
use App\Http\Api\Responses\Order\ScanEmptyBottleResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ScanEmptyBottleRequest;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/orders/{order}/scan-empty-bottle",
 *     summary="Scanner une bouteille vide retournée",
 *     description="Enregistre le retour d'une bouteille vide en scannant son code-barres. Seul le propriétaire de la commande (client) ou les livreurs peuvent effectuer cette action.",
 *     operationId="api.orders.scan-empty-bottle",
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
 *         description="Données du scan de bouteille vide",
 *         @OA\JsonContent(
 *             required={"barcode", "order_item_id"},
 *             @OA\Property(
 *                 property="barcode",
 *                 type="string",
 *                 description="Code-barres de la bouteille vide",
 *                 example="1234567890123"
 *             ),
 *             @OA\Property(
 *                 property="order_item_id",
 *                 type="integer",
 *                 description="ID de l'article de commande correspondant",
 *                 example=1
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Bouteille vide scannée avec succès",
 *         @OA\JsonContent(
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Empty bottle scanned successfully.")
 *             ),
 *             @OA\Property(property="data", type="object", nullable=true)
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
 *         description="Vous n'êtes pas autorisé à scanner des bouteilles pour cette commande",
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
 *         description="Données de validation invalides",
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     )
 * )
 */
final class ScanEmptyBottleController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    /**
     * Handle the incoming request.
     *
     * Route: POST /api/orders/{order}/scan-empty-bottle
     * Name: api.orders.scan-empty-bottle
     */
    public function __invoke(ScanEmptyBottleRequest $request, Order $order): ScanEmptyBottleResponse
    {
        $user = auth()->user();

        // Security check: Only order owner (customer) and delivery persons can scan bottles
        $isOrderOwner = $user->customer && $user->customer->id === $order->customer_id;
        $isDeliveryPerson = $user->hasRole('delivery_person');

        if (! $isOrderOwner && ! $isDeliveryPerson) {
            abort(403, 'Vous n\'êtes pas autorisé à scanner des bouteilles pour cette commande.');
        }

        $dto = new ScanEmptyBottleDTO(
            barcode: $request->validated('barcode'),
            orderItemId: (int) $request->validated('order_item_id'),
        );

        $success = $this->orderService->handleEmptyBottleReturn($dto, $order);

        if ($success) {
            return ScanEmptyBottleResponse::success(null, 'Empty bottle scanned successfully.');
        }

        return ScanEmptyBottleResponse::error('Failed to scan empty bottle.');
    }
}
