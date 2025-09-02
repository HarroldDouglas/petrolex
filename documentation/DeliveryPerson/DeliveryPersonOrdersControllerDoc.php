<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/delivery-persons/{deliveryPersonId}/orders",
 *     summary="Obtenir toutes les commandes d'un livreur spécifique",
 *     description="Récupère une liste paginée des commandes pour un livreur donné, avec filtrage optionnel par statut, numéro de commande et type de livraison.",
 *     operationId="api.delivery-persons.orders",
 *     tags={"Commandes"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="deliveryPersonId",
 *         in="path",
 *         required=true,
 *         description="ID du livreur",
 *
 *         @OA\Schema(type="integer", format="int64")
 *     ),
 *
 *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         required=false,
 *         description="Filtrer les commandes par statut",
 *
 *         @OA\Schema(type="string", enum={"confirmed", "in_progress", "delivered", "cancelled", "pending"})
 *     ),
 *
 *     @OA\Parameter(
 *         name="order_number",
 *         in="query",
 *         required=false,
 *         description="Filtrer les commandes par numéro de commande (correspondance partielle)",
 *
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Parameter(
 *         name="delivery_type",
 *         in="query",
 *         required=false,
 *         description="Filtrer les commandes par type de livraison",
 *
 *         @OA\Schema(type="string", enum={"normal", "fast"})
 *     ),
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Nombre de commandes par page",
 *
 *         @OA\Schema(type="integer", format="int32", minimum=1, default=10)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Opération réussie",
 *
 *         @OA\JsonContent(
 *             allOf={
 *
 *                 @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *                 @OA\Schema(
 *
 *                     @OA\Property(
 *                         property="data",
 *                         type="array",
 *
 *                         @OA\Items(ref="#/components/schemas/DeliveryPersonOrderData")
 *                     ),
 *
 *                     @OA\Property(property="message", type="string", example="Commandes du livreur récupérées avec succès")
 *                 )
 *             }
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Non autorisé",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Livreur introuvable",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreur de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur serveur interne",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class GetDeliveryPersonOrdersControllerDoc {}
