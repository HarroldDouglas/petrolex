<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Bottle;

use App\Http\Api\Resources\Bottle\BottleVerificationResource;
use App\Http\Api\Responses\ApiResponse;

final class BottleVerificationResponse extends ApiResponse
{
    /**
     * Return response for bottle verification.
     */
    public static function verified(
        array $data,
        ?string $message = null,
        int $statusCode = 200
    ): self {
        return new self(
            new BottleVerificationResource($data),
            $message ?? 'Résultat de la vérification de la bouteille',
            true,
            $statusCode
        );
    }
}
