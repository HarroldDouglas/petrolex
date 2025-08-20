<?php

declare(strict_types=1);

namespace App\Http\Api\Responses\Auth;

use App\Http\Api\Responses\ApiResponse;

final class UpdatePasswordResponse extends ApiResponse
{
    public static function success($data = null, ?string $message = null, int $statusCode = 200): self
    {
        return new self($data, $message ?? 'Mot de passe mis à jour avec succès.', true, $statusCode);
    }

    public static function error(?string $message = null, $data = null, int $statusCode = 400): self
    {
        return new self($data, $message ?? 'Échec de la mise à jour du mot de passe.', false, $statusCode);
    }
}
