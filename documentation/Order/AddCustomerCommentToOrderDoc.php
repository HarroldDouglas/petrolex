<?php

namespace App\Documentation\Order;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/orders/{order}/customer-feedback",
 *     summary="Add customer comment and rating to an order",
 *     tags={"Commandes"},
 *
 *     @OA\Parameter(
 *         name="order",
 *         in="path",
 *         required=true,
 *         description="ID of the order",
 *
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={"comments", "rating"},
 *
 *             @OA\Property(property="comments", type="string", example="The delivery was very fast and the service was excellent!"),
 *             @OA\Property(property="rating", type="number", format="float", example=4.5)
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Comment and rating added successfully",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="_metadata", type="object",
 *                 properties={
 *                     @OA\Property(property="success", type="boolean", example=true),
 *                     @OA\Property(property="message", type="string", example="Commentaire ajouté à la commande avec succès")
 *                 }
 *             ),
 *             @OA\Property(property="data", type="array", 
 *                 @OA\Items()
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Order not found"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     )
 * )
 */
class AddCustomerCommentToOrderDoc {}
