<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="BottleVerificationData",
 *
 *     @OA\Property(property="authentic", type="boolean", example=true, description="Indicates if the bottle is authentic and meets criteria.")
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
 *                 example="Bottle verification result"
 *             )
 *         )
 *     }
 * )
 *
 * @OA\Get(
 *     path="/api/bottles/{barcode}/verify",
 *     summary="Verify bottle authenticity",
 *     description="Checks if a bottle exists in the distribution center, has status WITH_DELIVERY_PERSON, and is_filled to true.",
 *     operationId="api.bottles.verify",
 *     tags={"Bottles"},
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
