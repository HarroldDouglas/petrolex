<?php

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="DeliveryAddress",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="label", type="string", example="Maison principale"),
 *     @OA\Property(property="address", type="string", example="123 Rue Principale"),
 *     @OA\Property(property="neighborhood", type="string", example="Bonapriso"),
 *     @OA\Property(property="city", type="string", example="Douala"),
 *     @OA\Property(property="country", type="string", example="Cameroun"),
 *     @OA\Property(property="latitude", type="number", format="float", example=4.0511),
 *     @OA\Property(property="longitude", type="number", format="float", example=9.7679),
 *     @OA\Property(property="phone", type="string", example="+237612345678"),
 *     @OA\Property(property="contact_name", type="string", example="Jean Dupont"),
 *     @OA\Property(property="is_default", type="boolean", example=true),
 * )
 */
class DeliveryAddress {}
