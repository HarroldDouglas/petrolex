<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="BottleVerificationData",
 *
 *     @OA\Property(property="authentic", type="boolean", example=true, description="Indique si la bouteille est authentique et respecte les critères.")
 * )
 *
 * @OA\Schema(
 *     schema="BottleVerificationResponse",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiResponse"),
 *         @OA\Schema(
 *
 *             @OA\Property(
 *                 property="data",
 *                 ref="#/components/schemas/BottleVerificationData"
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Résultat de vérification de la bouteille"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/bottles/{barcode}/verify",
 *     summary="Vérifier l'authenticité d'une bouteille",
 *     description="Vérifie si une bouteille existe dans le centre de distribution, a le statut WITH_DELIVERY_PERSON, et is_filled à true.",
 *     operationId="api.bottles.verify",
 *     tags={"Bouteilles"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="barcode",
 *         in="path",
 *         required=true,
 *         description="The barcode of the bottle to verify.",
 *
 *         @OA\Schema(type="string", example="BTG3SQ8FPZ")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Bottle verification successful",
 *
 *         @OA\JsonContent(ref="#/components/schemas/BottleVerificationResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *
 *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
 *     )
 * )
 */
class VerifyBottleControllerDoc {}
