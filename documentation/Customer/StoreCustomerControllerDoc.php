<?php

namespace App\Documentation\Customer;

use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/register/customer",
 *     summary="Inscription client",
 *     description="Enregistre un nouvel utilisateur en tant que client et envoie un code OTP pour vérification.",
 *     operationId="api.register.customer",
 *     tags={"Authentification"},
 *
 *     @OA\RequestBody(
 *         required=true,
 *
 *         @OA\JsonContent(
 *             required={
 *                 "first_name",
 *                 "last_name",
 *                 "email",
 *                 "email_confirmation",
 *                 "phone_number",
 *                 "password",
 *                 "country_code"
 *             },
 *
 *             @OA\Property(property="first_name", type="string", example="Jean"),
 *             @OA\Property(property="last_name", type="string", example="Dupont"),
 *             @OA\Property(property="email", type="string", format="email", example="test@example.com"),
 *             @OA\Property(property="email_confirmation", type="string", format="email", example="test@example.com", description="Doit correspondre exactement à l'email"),
 *             @OA\Property(property="phone_number", type="string", example="677123456", description="Numéro de téléphone sans code pays"),
 *             @OA\Property(property="country_code", type="string", example="CM", description="Code ISO du pays (ex: CM pour Cameroun)"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="language", type="string", enum={"fr", "en"}, nullable=true, example="fr", description="User's preferred language (fr for French, en for English). Defaults to 'fr' if not provided."),
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
 *                     @OA\Property(property="identifier", type="string", example="test@example.com")
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
