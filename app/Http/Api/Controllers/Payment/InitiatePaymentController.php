<?php

declare(strict_types=1);

namespace App\Http\Api\Controllers\Payment;

use App\Enums\PaymentMethod;
use App\Http\Api\Requests\Payment\InitiatePaymentRequest;
use App\Http\Api\Responses\Order\OrderDetailsResponse;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentService;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @OA\Post(
 *     path="/api/orders/{order}/payment",
 *     summary="Initier le paiement d'une commande",
 *     description="Lance le processus de paiement pour une commande spécifique avec la méthode de paiement choisie. Supporte Orange Money, MTN Money et cartes de crédit.",
 *     operationId="api.orders.initiate-payment",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID de la commande à payer",
 *
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             type="object",
 *             required={"payment_method"},
 *
 *             @OA\Property(
 *                 property="payment_method",
 *                 type="string",
 *                 enum={"orange_money", "mtn_money", "credit_card"},
 *                 example="orange_money",
 *                 description="Méthode de paiement"
 *             ),
 *             @OA\Property(
 *                 property="payment_details",
 *                 type="object",
 *                 description="Détails spécifiques à la méthode de paiement (phone+name pour mobile money, card_number+cvv+expiry_date+cardholder_name pour carte)",
 *                 @OA\Property(property="phone", type="string", example="677123456", description="Numéro de téléphone (mobile money)"),
 *                 @OA\Property(property="name", type="string", example="Jean Dupont", description="Nom du titulaire (mobile money)"),
 *                 @OA\Property(property="card_number", type="string", example="4111111111111111", description="Numéro de carte (carte de crédit)"),
 *                 @OA\Property(property="cvv", type="string", example="123", description="Code CVV (carte de crédit)"),
 *                 @OA\Property(property="expiry_date", type="string", example="12/25", description="Date d'expiration (carte de crédit)"),
 *                 @OA\Property(property="cardholder_name", type="string", example="Jean Dupont", description="Nom du titulaire (carte de crédit)")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Paiement initié avec succès - Retourne l'objet commande complet avec toutes ses relations",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(
 *                 property="_metadata",
 *                 type="object",
 *                 @OA\Property(property="success", type="boolean", example=true),
 *                 @OA\Property(property="message", type="string", example="Paiement initié avec succès.")
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="order",
 *                     ref="#/components/schemas/OrderDetailsData"
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Erreur - Commande ne peut pas accepter de paiement",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
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
 *         response=404,
 *         description="Commande non trouvée",
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
 */
final class InitiatePaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    public function __invoke(InitiatePaymentRequest $request, Order $order): OrderDetailsResponse
    {
        // Simplified authorization check for testing
        if ($order->customer_id !== $request->user()->customer->id) {
            throw new BadRequestHttpException(__('This order does not belong to you'));
        }

        if (! $order->canAcceptPayment()) {
            throw new BadRequestHttpException(__('order.cannot_accept_payment'));
        }

        $data = $request->validated();

        $payment = $this->paymentService->initiatePayment(
            $order,
            PaymentMethod::from($data['payment_method']),
            $data['payment_details']
        );

        // Reload order with all relations like GetOrderDetailsController
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

        return OrderDetailsResponse::withOrder($order, 'Paiement initié avec succès');
    }
}
