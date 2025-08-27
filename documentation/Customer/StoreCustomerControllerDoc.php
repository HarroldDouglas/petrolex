<?php

namespace App\Documentation\Customer;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/customers",
 *     summary="Créer un nouveau client",
 *     description="Enregistre un nouvel utilisateur en tant que client et envoie un code OTP pour vérification.",
 *     operationId="api.customers.store",
 *     tags={"Clients"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={
 *                 "first_name",
 *                 "last_name",
 *                 "email",
 *                 "phone_number",
 *                 "password"
 *             },
 *
 *             @OA\Property(property="first_name", type="string", example="Jean"),
 *             @OA\Property(property="last_name", type="string", example="Dupont"),
 *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
 *             @OA\Property(property="phone_number", type="string", example="+237677123456"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Client créé avec succès. Un OTP a été envoyé pour vérification.",
 *
 *         @OA\JsonContent(
 *
 *             @OA\Property(property="_metadata", type="object",
 *                 properties={
 *                     @OA\Property(property="success", type="boolean", example=true),
 *                     @OA\Property(property="message", type="string", example="Client created successfully. An OTP has been sent to your email/phone for verification.")
 *                 }
 *             ),
 *             @OA\Property(property="data", type="object",
 *                 properties={
 *                     @OA\Property(property="identifier", type="string", example="jean.dupont@example.com")
 *                 }
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Erreurs de validation",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Erreur interne du serveur",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class StoreCustomerControllerDoc {}
